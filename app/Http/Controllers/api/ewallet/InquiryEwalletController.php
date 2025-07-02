<?php

namespace App\Http\Controllers\api\ewallet;

use App\Http\Controllers\Controller;
use App\Models\Helper\HttpUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

use App\Models\Helper\Helpers;
use Carbon\Carbon;
use Throwable;

class InquiryEwalletController extends Controller
{
    // Inquiry Ewallet Endpoint
    protected $useProd = false;
    protected $useCloud = false;
    protected $inquiry_ewallet_endpoint = "/api/v1.0/debit/status";

    // Credential
    protected $client_id = "";

    PROTECTED $key = "-----BEGIN RSA PRIVATE KEY-----" . "\r\n" .
    "" . // string private key
    "\r\n" .
    "-----END RSA PRIVATE KEY-----";
    PROTECTED $client_secret = ""; // string client secret
    PROTECTED $access_token = ""; // string access token
    PROTECTED $store_id = "";

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * inquiry transaction ewallet
     * check transaction status
     *
     * @return JsonResponse
     */
    public function inquiryEwallet(): JsonResponse
    {
        $url = HttpUtil::getNicepayDomain($this->useProd, $this->useCloud) . $this->inquiry_ewallet_endpoint;
        $helper = new Helpers();
        $http_method = "POST";
        $date = Carbon::now();
        $x_time_stamp = $date->toIso8601String();
        $time_stamp = $date->format("YmdHis");
        $partner_id = $this->client_id; //merchantId
        $client_secret = $this->client_secret;
        $access_token = $this->access_token;
        $store_id = $this->store_id;

        $external_id = "MrEwTst" . $time_stamp . Str::random(5);
        $original_reference_no = "TNICEEW05105202408081348437590";
        $reference_no = "refNoEw20240808134839IXL1X";

        $amount = [
            "value" => "11.00",
            "currency" => "IDR"
        ];
        $additionalInfo = new \stdClass();

        $body = [
            "merchantId" => $partner_id,
            "subMerchantId" => $partner_id,
            "originalPartnerReferenceNo" => $reference_no,
            "originalReferenceNo" => $original_reference_no,
            "serviceCode" => "54",
            "transactionDate" => $x_time_stamp,
            "externalStoreId" => $store_id,
            "amount" => $amount,
            "additionalInfo" => $additionalInfo
        ];

        $string_to_sign = $helper->generateStringToSign(
                $http_method,
                $this->inquiry_ewallet_endpoint,
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
                $partner_id . "08"
            );
        print_r($string_to_sign);

        print_r("\r\n");

        print_r($header);
        print_r($body);

        try {
            $response = HttpUtil::postJsonRequestWithHeader($url, $body, $header);
            return response()->json([
                'status' => $response->status(),
                'message' => $response->successful(),
                'data' => $response
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => "Internal Server Error",
                'data' => $th
            ]);
        }
    }
}
