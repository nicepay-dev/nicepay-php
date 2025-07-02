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
    protected $client_id = "NORMALTEST";
    protected $base_url = "https://dev.nicepay.co.id/nicepay/v1.0/access-token/b2b";
    PROTECTED $key = "-----BEGIN RSA PRIVATE KEY-----" . "\r\n" .
    "MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAInJe1G22R2fMchIE6BjtYRqyMj6lurP/zq6vy79WaiGKt0Fxs4q3Ab4ifmOXd97ynS5f0JRfIqakXDcV/e2rx9bFdsS2HORY7o5At7D5E3tkyNM9smI/7dk8d3O0fyeZyrmPMySghzgkR3oMEDW1TCD5q63Hh/oq0LKZ/4Jjcb9AgMBAAECgYA4Boz2NPsjaE+9uFECrohoR2NNFVe4Msr8/mIuoSWLuMJFDMxBmHvO+dBggNr6vEMeIy7zsF6LnT32PiImv0mFRY5fRD5iLAAlIdh8ux9NXDIHgyera/PW4nyMaz2uC67MRm7uhCTKfDAJK7LXqrNVDlIBFdweH5uzmrPBn77foQJBAMPCnCzR9vIfqbk7gQaA0hVnXL3qBQPMmHaeIk0BMAfXTVq37PUfryo+80XXgEP1mN/e7f10GDUPFiVw6Wfwz38CQQC0L+xoxraftGnwFcVN1cK/MwqGS+DYNXnddo7Hu3+RShUjCz5E5NzVWH5yHu0E0Zt3sdYD2t7u7HSr9wn96OeDAkEApzB6eb0JD1kDd3PeilNTGXyhtIE9rzT5sbT0zpeJEelL44LaGa/pxkblNm0K2v/ShMC8uY6Bbi9oVqnMbj04uQJAJDIgTmfkla5bPZRR/zG6nkf1jEa/0w7i/R7szaiXlqsIFfMTPimvRtgxBmG6ASbOETxTHpEgCWTMhyLoCe54WwJATmPDSXk4APUQNvX5rr5OSfGWEOo67cKBvp5Wst+tpvc6AbIJeiRFlKF4fXYTb6HtiuulgwQNePuvlzlt2Q8hqQ==" . // string private key
    "\r\n" .
    "-----END RSA PRIVATE KEY-----";

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
