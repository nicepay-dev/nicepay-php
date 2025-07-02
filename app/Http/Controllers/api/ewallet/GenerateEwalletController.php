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

class GenerateEwalletController extends Controller
{
    // Ewallet Endpoint
    protected $useProd = false;
    protected $useCloud = false;
    protected $payment_host_to_host_endpoint = "/api/v1.0/debit/payment-host-to-host";

    // Credential
    protected $client_id = "";
    protected $domain = "https://dev.nicepay.co.id/nicepay";
    PROTECTED $key = "-----BEGIN RSA PRIVATE KEY-----" . "\r\n" .
    "" . // string private key
    "\r\n" .
    "-----END RSA PRIVATE KEY-----";
    PROTECTED $client_secret = ""; // string client secret
    PROTECTED $access_token = ""; // string access token
    PROTECTED $store_id = "";

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
     * generate transaction using ewallet
     *
     * @return JsonResponse
     */
    public function generateEwallet(): JsonResponse
    {
        $url = HttpUtil::getNicepayDomain($this->useProd, $this->useCloud) . $this->payment_host_to_host_endpoint;

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

        $external_id = "MrEwTst" . $time_stamp . Str::random(5);
        $reference_no = "refNoEw" . $time_stamp . Str::random(5);


        $items = array();

        $itemA = [
            "img_url" => "https://d3nevzfk7ii3be.cloudfront.net/igi/vOrGHXlovukA566A.medium",
            "goods_name" => "Nokia 3360",
            "goods_detail" => "Old Nokia 3360",
            "goods_amt" => "10.00",
            "goods_quantity" => "1"
        ];
        array_push($items, $itemA);
        $itemB = [
            "img_url" => "https://d3nevzfk7ii3be.cloudfront.net/igi/vOrGHXlovukA566A.medium",
            "goods_name" => "Nokia 3360",
            "goods_detail" => "Old Nokia 3360",
            "goods_amt" => "1.00",
            "goods_quantity" => "1"
        ];
        array_push($items, $itemB);
        $countAmt = 0;
        $countItm = 0;
        foreach ($items as $itm) {
            $amt = $itm["goods_amt"];
            str_replace(".00", "", $amt);

            $countAmt += (int) $itm["goods_quantity"] * (int) $amt;
            $countItm++;
        }

        $cartData = [
            "count" => "$countItm",
            "item" => $items
        ];

        $totalAmount = [
            "value" => $countAmt . ".00",
            "currency" => "IDR"
        ];

        $additionalInfo = [
            "mitraCd" => "OVOE",
            "goodsNm" => "Merchant Goods 1",
            "billingNm" => "SNAP Ewallet",
            "billingPhone" => "08123456789",
            "dbProcessUrl" => "https://ptsv2.com/t/jhon/post",
            "callBackUrl"=> "https://ptsv2.com/t/jhon/post",
            "cartData" => json_encode($cartData)
        ];

        $urlParam = array();
        $paramNotify = [
            "url" => "https://ptsv2.com/t/jhon/post",
            "type" => "PAY_NOTIFY",
            "isDeeplink" => "Y"
        ];
        array_push($urlParam, $paramNotify);
        $paramReturn = [
            "url" => "https://ptsv2.com/t/jhon/post",
            "type" => "PAY_RETURN",
            "isDeeplink" => "Y"
        ];
        array_push($urlParam, $paramReturn);

        $body = [
            "partnerReferenceNo" => $reference_no,
            "merchantId" => $partner_id,
            "subMerchantId" => $partner_id,
            "amount" => $totalAmount,
            "urlParam"=> $urlParam,
            "externalStoreId" => $store_id,
            "validUpTo" => $validity_period,
            "additionalInfo" => $additionalInfo
        ];

        $string_to_sign = $helper->generateStringToSign(
                $http_method,
                $this->payment_host_to_host_endpoint,
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

        print_r(json_encode($header));
        print_r("\r\n");
        print_r(json_encode($body));
        print_r("\r\n");

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
