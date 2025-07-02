<?php

namespace App\Http\Controllers\api\v2;

use App\Http\Controllers\Controller;
use App\Models\Helper\HttpUtil;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Throwable;

class EwalletV2Controller extends Controller
{
    // Ewallet Endpoint
    protected $useProd = false;
    protected $useCloud = false;
    protected $registration_endpoint = "/nicepay/direct/v2/registration";
    protected $inquiry_endpoint = "/nicepay/direct/v2/inquiry";
    protected $cancel_endpoint = "/nicepay/direct/v2/cancel";
    protected $payment_endpoint ="/nicepay/direct/v2/payment";

    // Credential Ewallet
    protected $imid = "TNICEEW051";
    protected $mer_key = "33F49GnCMS1mFYlGXisbUDzVf2ATWCl9k3R++d5hDd3Frmuos/XLx8XhXpe+LDYAbpGKZYSwtlyyLOtS/8aD7A==";

    // Constant
    protected $pay_method = "05";
    protected $amt = "1000";


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Registration for E wallet transaction
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
            "mitraCd" => "DANA",
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
            "deliveryNm" => "omen@example.com",
            "deliveryPhone" => "081288998899",
            "deliveryAddr" => "Jln. Raya Kasablanka Kav.88",
            "deliveryCity" => "South Jakarta",
            "deliveryState" => "DKI Jakarta",
            "deliveryPostCd" => "12800",
            "deliveryCountry" => "Indonesia",
            "description" => "Testing",
            "reqDomain" => "merchant.com",
            "reqServerIP" => "127.0.0.1",
            "userIP" => "127.0.0.1",
            "userSessionID" => "697D6922C961070967D3BA1BA5699C2C",
            "userAgent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML,like Gecko) Chrome/60.0.3112.101 Safari/537.36",
            "userLanguage" => "ko-KR,en-US;q=0.8,ko;q=0.6,en;q=0.4",
            "vat" => "0",
            "fee" => "0",
            "notaxAmt" => $amt,
            "shopId" => ""
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
        $txid = "TNICEEW05105202507021955552388";           // TODO : Fill with the registered transaction
        $reference_no = "OrderNo12601";                // TODO : Fill with the registered transaction
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
        $txid = "TNICEEW05105202507022002322744";
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
            return HttpUtil::generateErrorResponse($th);
        }
    }

    public function payment(): JsonResponse
    {
        $url = HttpUtil::getNicepayDomain($this->useProd, $this->useCloud) . $this->payment_endpoint;

        // Request body parameter
        $txid = "TNICEEW05105202507022002322744";
        $timestamp = Carbon::now()->format("YmdHis");
        $reference_no = "OrderNo16622";
        $amt = $this->amt;
        $merchant_token = $this->generateMerchantToken($timestamp, $this->imid, $reference_no, $amt, $this->mer_key);

        $param = [
            "timeStamp" => $timestamp,
            "tXid" => $txid,
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

    private function generateMerchantToken($timestamp, $imid, $transactionId, $amt, $mer_key): string
    {
        $merchant_data = $timestamp . $imid . $transactionId . $amt . $mer_key;
        return hash("sha256", $merchant_data);
    }
}
