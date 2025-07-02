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
    protected $client_id = "NORMALTEST";
    PROTECTED $key = "-----BEGIN RSA PRIVATE KEY-----" . "\r\n" .
    "MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAInJe1G22R2fMchIE6BjtYRqyMj6lurP/zq6vy79WaiGKt0Fxs4q3Ab4ifmOXd97ynS5f0JRfIqakXDcV/e2rx9bFdsS2HORY7o5At7D5E3tkyNM9smI/7dk8d3O0fyeZyrmPMySghzgkR3oMEDW1TCD5q63Hh/oq0LKZ/4Jjcb9AgMBAAECgYA4Boz2NPsjaE+9uFECrohoR2NNFVe4Msr8/mIuoSWLuMJFDMxBmHvO+dBggNr6vEMeIy7zsF6LnT32PiImv0mFRY5fRD5iLAAlIdh8ux9NXDIHgyera/PW4nyMaz2uC67MRm7uhCTKfDAJK7LXqrNVDlIBFdweH5uzmrPBn77foQJBAMPCnCzR9vIfqbk7gQaA0hVnXL3qBQPMmHaeIk0BMAfXTVq37PUfryo+80XXgEP1mN/e7f10GDUPFiVw6Wfwz38CQQC0L+xoxraftGnwFcVN1cK/MwqGS+DYNXnddo7Hu3+RShUjCz5E5NzVWH5yHu0E0Zt3sdYD2t7u7HSr9wn96OeDAkEApzB6eb0JD1kDd3PeilNTGXyhtIE9rzT5sbT0zpeJEelL44LaGa/pxkblNm0K2v/ShMC8uY6Bbi9oVqnMbj04uQJAJDIgTmfkla5bPZRR/zG6nkf1jEa/0w7i/R7szaiXlqsIFfMTPimvRtgxBmG6ASbOETxTHpEgCWTMhyLoCe54WwJATmPDSXk4APUQNvX5rr5OSfGWEOo67cKBvp5Wst+tpvc6AbIJeiRFlKF4fXYTb6HtiuulgwQNePuvlzlt2Q8hqQ==" . // string private key
    "\r\n" .
    "-----END RSA PRIVATE KEY-----";

    PROTECTED $client_secret = "1af9014925cab04606b2e77a7536cb0d5c51353924a966e503953e010234108a"; // string CLIENT SECRET
    PROTECTED $access_token = "";

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
