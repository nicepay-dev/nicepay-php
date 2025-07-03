<?php

namespace App\Http\Controllers\api\accessToken;

use App\Http\Controllers\Controller;
use App\Models\Helper\HttpUtil;
use Illuminate\Support\Facades\Http;

use App\Models\Helper\Helpers;
use Carbon\Carbon;
use Throwable;

class GenerateAccessTokenController extends Controller
{
    // Access Token Endpoint
    protected $useProd = false;
    protected $useCloud = false;
    protected $access_token_endpoint = "/nicepay/v1.0/access-token/b2b";

    // Credential
    protected $client_id = "CLIENT_KEY_MERCHANT";
    PROTECTED $key = "_PRIVATE_KEY_MERCHANT"; //string private key


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * generate access token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateAccessToken()
    {
        global $response;
        $url = HttpUtil::getNicepayDomain($this->useProd, $this->useCloud) . $this->access_token_endpoint;

        $helper = new Helpers();
        $x_time_stamp = Carbon::now()->toIso8601String();
        $client_id = $this->client_id;

        $addInfo = new \stdClass();

        $bd = [
            "grantType" => "client_credentials",
            "additionalInfo" => json_encode($addInfo)
        ];

        $string_to_sign = $client_id . "|" . $x_time_stamp;
        print_r($string_to_sign);
        print_r("\r\n");

        $signature = $helper->generateSignature($string_to_sign, $this->key, OPENSSL_ALGO_SHA256);
        print_r($signature);
        print_r("\r\n");

        $header = $helper->generateHeaderAccessToken($x_time_stamp, $client_id, $signature);
        print_r($header);
        print_r("\r\n");

        try {
            $response = Http::withHeaders($header)->post($url, $bd);
        } catch (Throwable $th) {
            return response()->json([
                'status' => $response->status(),
                'message' => $response->successful(),
                'data' => $response->json()
            ]);
        }

        return response()->json([
            'status' => $response->status(),
            'message' => $response->successful(),
            'data' => $response->json()
        ]);
    }

    /**
     * @throws Throwable
     */
    public function generateAccessTokenWithParam($client_id, $private_key)
    {
        $url = HttpUtil::getNicepayDomain($this->useProd, $this->useCloud) . $this->access_token_endpoint;

        $helper = new Helpers();
        $x_time_stamp = Carbon::now()->toIso8601String();

        $addInfo = new \stdClass();

        $bd = [
            "grantType" => "client_credentials",
            "additionalInfo" => json_encode($addInfo)
        ];

        $string_to_sign = $client_id . "|" . $x_time_stamp;
        print_r($string_to_sign);
        print_r("\r\n");

        $signature = $helper->generateSignature($string_to_sign, $private_key, OPENSSL_ALGO_SHA256);
        print_r($signature);
        print_r("\r\n");

        $header = $helper->generateHeaderAccessToken($x_time_stamp, $client_id, $signature);
        print_r($header);
        print_r("\r\n");

        try {
            $response = Http::withHeaders($header)->post($url, $bd);
            $responseData = $response->json();

            if (isset($responseData['responseCode']) && $responseData['responseCode'] === '2007300') {
                return $responseData['accessToken'];
            } else {
                throw new \Exception("Generate access token fail");
            }
        } catch (Throwable $th) {
            throw $th;
        }
    }
}
