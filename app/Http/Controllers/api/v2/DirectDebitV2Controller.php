<?php

namespace App\Http\Controllers\api\v2;

use App\Http\Controllers\Controller;
use App\Models\Helper\HttpUtil;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Throwable;

class DirectDebitV2Controller extends Controller
{
    // Direct Debit Endpoint
    protected $useProd = false;
    protected $useCloud = false;
    protected $registration_endpoint = "/nicepay/direct/v2/registration";
    protected $inquiry_endpoint = "/nicepay/direct/v2/inquiry";
    protected $cancel_endpoint = "/nicepay/direct/v2/cancel";
    protected $payment_endpoint ="/nicepay/direct/v2/payment";

    // Credential clickpay
    protected $imid = "TNICECP041";
    protected $mer_key = "33F49GnCMS1mFYlGXisbUDzVf2ATWCl9k3R++d5hDd3Frmuos/XLx8XhXpe+LDYAbpGKZYSwtlyyLOtS/8aD7A==";

    // Constant
    protected $pay_method = "04";
    protected $amt = "10000";

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Registration for direct debit transaction
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
            "mitraCd" => "JENC",
            "currency" => "IDR",
            "amt" => $this->amt,
            "referenceNo" => $reference_no,
            "merchantToken" => $merchant_token,
            "dbProcessUrl" => "https://webhook.site/90aa57b4-9bdd-4f3c-bf0a-ca35d78897b0",
            "goodsNm" => "Goods",
            "cartData" => $cartData,
            "billingNm" => "John Test",
            "billingPhone" => "081363681274",
            "billingEmail" => "omen@example.com",
            "billingAddr" => "Jln. Raya Kasablanka Kav.88",
            "billingCity" => "South Jakarta",
            "billingState" => "DKI Jakarta",
            "billingPostCd" => "15119",
            "billingCountry" => "Indonesia",
            "description" => "Testing",
            "userIP" => "127.0.0.1",
        ];

        try {
            $response = HttpUtil::postJsonRequest($url, $body);

            return response()->json([
                'status' => $response->status(),
                'data' => $response->json()
            ]);
        } catch (Throwable $th) {
            return HttpUtil::generateErrorResponse($th);
        }
    }

    public function inquiry(): JsonResponse
    {
        // Endpoint
        $url = HttpUtil::getNicepayDomain($this->useProd, $this->useCloud) . $this->inquiry_endpoint;

        // Request body parameter
        $timestamp = Carbon::now()->format("YmdHis");
        $txid = "TNICECP04104202507021954332327";           // TODO : Fill with the registered transaction
        $reference_no = "OrderNo11108";                      // TODO : Fill with the registered transaction
        $amt = $this->amt;                                  // TODO : Fill with the registered transaction
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
            return HttpUtil::generateErrorResponse($th);
        }
    }

    public function cancel(): JsonResponse
    {
        $url = HttpUtil::getNicepayDomain($this->useProd, $this->useCloud) . $this->cancel_endpoint;

        //request body parameter
        $timestamp = Carbon::now()->format("YmdHis");
        $txid = "";
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
            "cancelRetryCnt" => "",
            "cancelUserInfo" => ""
        ];
        try {
            $response = HttpUtil::postJsonRequest($url, $body);

            return response()->json([
                'status' => $response->status(),
                'data' => $response->json()
            ]);
        } catch (Throwable $th) {
            return HttpUtil::generateErrorResponse($th);
        }
    }

    public function payment(): JsonResponse
    {
        $url = HttpUtil::getNicepayDomain($this->useProd, $this->useCloud) . $this->payment_endpoint;

        // Request body parameter
        $txid = "TNICECP04104202506301306549921";
        $timestamp = Carbon::now()->format("YmdHis");
        $reference_no = "OrderNo8932";
        $amt = $this->amt;
        $merchant_token = $this->generateMerchantToken($timestamp, $this->imid, $reference_no, $amt, $this->mer_key);

        $param = [
            "timeStamp" => $timestamp,
            "tXid" => $txid,
            "cashtag" => "\$jeniuspay001",
            "merchantToken" => $merchant_token,
            "callBackUrl" => "https://dev.nicepay.co.id/IONPAY_CLIENT/paymentResult.jsp",
            "returnJsonFormat" => "1",
        ];

        try {
            HttpUtil::redirectWithQueryParams($url, $param);
            return HttpUtil::generateSuccessRedirectResponse();
        } catch (Throwable $th) {
            return HttpUtil::generateErrorResponse($th);
        }
    }

    public function generateCartData($amt)
    {
        return json_encode([
            "count" => "2",
            "item" => [
                [
                    "img_url" => "https://d3nevzfk7ii3be.cloudfront.net/igi/vOrGHXlovukA566A.medium",
                    "goods_name" => "Nokia 3360",
                    "goods_detail" => "Old Nokia 3360",
                    "goods_amt" => (string) $amt,
                    "goods_quantity" => "1"
                ],
                [
                    "img_url" => "https://d3nevzfk7ii3be.cloudfront.net/igi/vOrGHXlovukA566A.medium",
                    "goods_name" => "Nokia 3360",
                    "goods_detail" => "Old Nokia 3360",
                    "goods_amt" => "0",
                    "goods_quantity" => "1"
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
