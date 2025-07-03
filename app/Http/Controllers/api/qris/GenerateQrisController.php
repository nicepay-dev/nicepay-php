<?php

namespace App\Http\Controllers\api\qris;

use App\Http\Controllers\Controller;
use App\Models\Helper\HttpUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

use App\Models\Helper\Helpers;
use Carbon\Carbon;

class GenerateQrisController extends Controller
{
    // Generate QRIS Endpoint
    protected $useProd = false;
    protected $useCloud = false;
    protected $create_qris_endpoint = "/api/v1.0/qr/qr-mpm-generate";

    // Credential
    protected $client_id = "CLIENT_KEY_MERCHANT";
    PROTECTED $key = "_PRIVATE_KEY_MERCHANT"; // string private key
    PROTECTED $client_secret = "_CLIENT_SECRET_MERCHANT"; // string CLIENT SECRET
    PROTECTED $access_token = "_ACCESS_TOKEN_MERCHANT";
    PROTECTED $store_id = "_STORE_ID_MERCHANT";

    // for amount
    PROTECTED $amt = "100.00";
    /*
     * if want to partial refund (not full partial),
     * need to change amount manual in refundQris function
     * */
    PROTECTED $cancel_type = 1;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * generate transaction qris
     *
     * @return JsonResponse
     */
    public function generateQris(): JsonResponse
    {
        $url = HttpUtil::getNicepayDomain($this->useProd, $this->useCloud) . $this->create_qris_endpoint;
        $helper = new Helpers();

        $http_method = "POST";
        $date = Carbon::now();
        $x_time_stamp = $date->toIso8601String();
        $time_stamp = $date->format("YmdHis");
        $validity_period = $date->addMinutes("5")->addSeconds("30")->toIso8601String();


        $partner_id = $this->client_id; //merchantId
        $client_secret = $this->client_secret;
        $access_token = $this->access_token;
        $store_id = $this->store_id;
        $result = array();

        $external_id = "MrQrTst" . $time_stamp . Str::random(5);
        $reference_no = "refNoQr" . $time_stamp . Str::random(5);

        $totalAmount = [
            "value" => $this->amt,
            "currency" => "IDR"
        ];

        $cartData = [
            "count" => "1",
            "item" => [
                "img_url" => "https://d3nevzfk7ii3be.cloudfront.net/igi/vOrGHXlovukA566A.medium",
                "goods_name" => "Nokia 3360",
                "goods_detail" => "Old Nokia 3360",
                "goods_amount" => $this->amt,
                "goods_quantity" => "1"
            ]
        ];

        $additionalInfo = [
            "goodsNm" => "QRIS",
            "billingNm" => "QRIS",
            "billingPhone" => "",
            "billingEmail" => "email@qris.com",
            "billingCity" => "Jakarta Selatan",
            "billingState" => "Jakarta",
            "billingPostCd" => "12870",
            "billingCountry" => "Indonesia",
            "dbProcessUrl" => "https://ptsv2.com/t/jhon/post",
            "callBackUrl"=> "https://ptsv2.com/t/jhon/post",
            "userIP" => "127.0.0.1",
            "cartData" => json_encode($cartData),
            "mitraCd" => "QSHP"
        ];

        $body = [
            "partnerReferenceNo" => $reference_no,
            "amount" => $totalAmount,
            "merchantId" => $partner_id,
            "storeId" => $store_id,
            // "validityPeriod" => "", //optional, if not used, will set default setting from nicepay 5 minutes
            "validityPeriod" => $validity_period, //optional, if not used, will set default setting from nicepay 5
            "additionalInfo" => $additionalInfo
        ];

        $string_to_sign = $helper->generateStringToSign(
                $http_method,
                $this->create_qris_endpoint,
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
