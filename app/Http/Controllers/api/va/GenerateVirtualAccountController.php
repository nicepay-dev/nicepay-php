<?php

namespace App\Http\Controllers\api\va;

use App\Http\Controllers\Controller;
use App\Models\Helper\HttpUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

use App\Models\Helper\Helpers;
use App\Models\va\RequestVA;
use Carbon\Carbon;

class GenerateVirtualAccountController extends Controller
{
    // Generate VA Endpoint
    protected $useProd = false;
    protected $useCloud = false;
    protected $create_va_endpoint = "/api/v1.0/transfer-va/create-va";

    // Credential
    protected $client_id = "CLIENT_KEY_MERCHANT";
    PROTECTED $key = "_PRIVATE_KEY_MERCHANT"; // string private key

    PROTECTED $client_secret = "_CLIENT_SECRET_MERCHANT"; // string CLIENT SECRET
    PROTECTED $access_token = "_ACCESS_TOKEN_MERCHANT";

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * generate virtual account
     *
     * @return JsonResponse
     */
    public function generateVirtualAccount(): JsonResponse
    {
        $url = HttpUtil::getNicepayDomain($this->useProd, $this->useCloud) . "/nicepay" . $this->create_va_endpoint;

        $helper = new Helpers();
        $http_method = "POST";
        $date = Carbon::now();
        $x_time_stamp = $date->toIso8601String();
        $time_stamp = $date->format("YmdHis");
        $partner_id = $this->client_id;
        $client_secret = $this->client_secret;
        $access_token = $helper->generateAccessToken($this->client_id, $this->key);
//        $access_token = $this->access_token;
        $external_id = "MrVATst" . $time_stamp . Str::random(5);

        $totalAmount = [
            "value" => "15000.00",
            "currency" => "IDR"
        ];

        $additionalInfo = [
            "bankCd" => "CENA",
            "goodsNm" => "CENA",
            "dbProcessUrl" => "https://ptsv2.com/t/jhon/post",
            "vacctValidDt" => "",
            "vacctValidTm" => "",
            "msId" => "",
            "msFee" => "",
            "mbFee" => "",
            "mbFeeType" => ""
        ];

        $body = [
            "partnerServiceId" => "",
            "customerNo" => "", //for fix
            "virtualAccountNo" => "",
            "virtualAccountName" => "Testing Create Virtual Account Nicepay",
            "trxId" => "trxIdVa" . $time_stamp,
            "totalAmount" => $totalAmount,
            "additionalInfo" => $additionalInfo
        ];

        $string_to_sign = $helper->generateStringToSign(
                $http_method,
                $this->create_va_endpoint,
                $access_token,
                $body,
                $x_time_stamp
            );

        $signature = $helper->hmacSHA512Encoded(
                $string_to_sign,
                $client_secret,
                OPENSSL_ALGO_SHA512
            );

        $header = $helper->generateHeader(
                $access_token,
                $x_time_stamp,
                $signature,
                $partner_id,
                $external_id,
                $partner_id . "02"
            );

        print_r($string_to_sign);
        print_r("\r\n");
        print_r($header);
        print_r($body);

        try {
            $response = HttpUtil::postJsonRequestWithHeader($url, $body, $header);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => "Internal Server Error",
                'data' => $th
            ]);
        }

        return response()->json([
            'status' => $response->status(),
            'message' => $response->successful(),
            'data' => $response
        ]);
    }
}
