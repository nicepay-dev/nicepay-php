<?php

namespace App\Http\Controllers\api\v2;

use App\Http\Controllers\Controller;
use App\Models\Helper\HttpUtil;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Throwable;

class VirtualAccountV2Controller extends Controller
{
    // VA Endpoint
    protected $useProd = false;
    protected $useCloud = false;
    protected $registration_endpoint = "/nicepay/direct/v2/registration";
    protected $inquiry_endpoint = "/nicepay/direct/v2/inquiry";
    protected $cancel_endpoint = "/nicepay/direct/v2/cancel";

    // Credential
    protected $imid = "_IMID_MERCHANT";
    protected $mer_key = "_MER_KEY_MERCHANT";

    // Constant
    protected $pay_method = "02";
    protected $amt = "15000";

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
    public function registration(): JsonResponse
    {
        // Endpoint
        $url = HttpUtil::getNicepayDomain($this->useProd, $this->useCloud) . $this->registration_endpoint;

        // Request body parameter
        $timestamp = Carbon::now()->format("YmdHis");
        $reference_no = "OrderNo" . rand(1, 100000);
        $amt = $this->amt;
        $merchant_token = $this->generateMerchantToken($timestamp, $this->imid, $reference_no, $amt, $this->mer_key);
        $cartData = $this->generateCartData($amt);

        $body = [
            "timeStamp" => $timestamp,
            "iMid" => $this->imid,
            "payMethod" => $this->pay_method,
            "currency" => "IDR",
            "bankCd" => "BMRI",
            "amt" => $this->amt,
            "referenceNo" => $reference_no,
            "merchantToken" => $merchant_token,
            "merFixAcctId" => "",
            "dbProcessUrl" => "https://webhook.site/90aa57b4-9bdd-4f3c-bf0a-ca35d78897b0",
            "goodsNm" => "Goods",
            "cartData" => $cartData,
            "description" => "",
            "billingNm" => "John Test",
            "billingPhone" => "",
            "billingEmail" => "omen@example.com",
            "billingAddr" => "Jln. Raya Kasablanka Kav.88",
            "billingCity" => "South Jakarta",
            "billingState" => "DKI Jakarta",
            "billingPostCd" => "15119",
            "billingCountry" => "Indonesia",
            "userIP" => "127.0.0.1",
            "vat" => "0",
            "fee" => "0",
            "vacctValidDt" => "",
            "vacctValidTm" => "",

        ];

        try {
            $response = HttpUtil::postJsonRequest($url, $body);

            return response()->json([
                'status' => $response->status(),
                'data' => $response->json()
            ]);
        } catch (Throwable $th) {
            return $this->generateErrorResponse($th);
        }
    }

    public function inquiry(): JsonResponse
    {
        // Endpoint
        $url = HttpUtil::getNicepayDomain($this->useProd, $this->useCloud) . $this->inquiry_endpoint;

        // Request body parameter
        $timestamp = Carbon::now()->format("YmdHis");
        $txid = "TNICEVA02302202507021945221860";           // TODO : Fill with the registered transaction
        $reference_no = "OrderNo39932";                // TODO : Fill with the registered transaction
        $amt = $this->amt;                // TODO : Fill with the registered transaction
        $merchant_token = $this->generateMerchantToken($timestamp, $this->imid, $reference_no, $this->amt, $this->mer_key);

        $body = [
            "timeStamp" => $timestamp,
            "tXid" => $txid,
            "iMid" => $this->imid,
            "merchantToken" => $merchant_token,
            "referenceNo" => $reference_no,
            "amt" => $amt,
        ];
        try {
            $response = HttpUtil::postJsonRequest($url, $body);

            return response()->json([
                'status' => $response->status(),
                'data' => $response->json()
            ]);
        } catch (Throwable $th) {
            return $this->generateErrorResponse($th);
        }
    }

    public function cancel(): JsonResponse
    {
        $url = HttpUtil::getNicepayDomain($this->useProd, $this->useCloud) . $this->cancel_endpoint;

        //request body parameter
        $timestamp = Carbon::now()->format("YmdHis");
        $txid = "TNICEVA02302202507021945221860";
        $amt = $this->amt;
        $merchant_token = $this->generateMerchantToken($timestamp, $this->imid, $txid, $this->amt, $this->mer_key);

        $body = [
            "timeStamp" => $timestamp,
            "tXid" => $txid,
            "iMid" => $this->imid,
            "payMethod" => $this->pay_method,
            "merchantToken" => $merchant_token,
            "amt" => $amt,
            "cancelMsg" => "Testing Cancellation",
            "cancelType" => "1",
            "cancelServerIp" => "127.0.0.1",
            "cancelUserId" => "Omen",
            "cancelUserIp" => "127.0.0.1",
        ];
        try {
            $response = HttpUtil::postJsonRequest($url, $body);

            return response()->json([
                'status' => $response->status(),
                'data' => $response->json()
            ]);
        } catch (Throwable $th) {
            return $this->generateErrorResponse($th);
        }
    }

    public function generateErrorResponse(Throwable $th)
    {
        print_r("Exception : " . $th);

        return response()->json([
            'status' => 500,
            'message' => "Internal Server Error",
            'data' => $th
        ]);
    }

    public function generateCartData($amt)
    {
        return json_encode([
            "count" => "1",
            "item" => [
                [
                    "goods_id" => "BB12345678",
                    "goods_detail" => "BB123456",
                    "goods_name" => "iPhone5S",
                    "goods_amt" => $amt,
                    "goods_type" => "Smartphone",
                    "goods_url" => "http://merchant.com/cellphones/iphone5s_64g",
                    "goods_quantity" => "1",
                    "goods_sellers_id" => "SEL123",
                    "goods_sellers_name" => "Sellers1"
                ]
            ]
        ]);
    }

    private function generateMerchantToken($timestamp, $imid, $transactionId, $amt, $mer_key)
    {
        $merchant_data = $timestamp . $imid . $transactionId . $amt . $mer_key;
        return hash("sha256", $merchant_data);
    }
}
