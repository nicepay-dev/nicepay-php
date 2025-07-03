<?php

namespace App\Http\Controllers\api\v2;

use App\Http\Controllers\Controller;
use App\Models\Helper\Helpers;
use App\Models\Helper\HttpUtil;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class PayloanV2Controller extends Controller
{
    // Payloan Endpoint
    protected $useProd = false;
    protected $useCloud = false;
    protected $registration_endpoint = "/nicepay/direct/v2/registration";
    protected $inquiry_endpoint = "/nicepay/direct/v2/inquiry";
    protected $cancel_endpoint = "/nicepay/direct/v2/cancel";
    protected $payment_endpoint ="/nicepay/direct/v2/payment";

    // Credential
    protected $imid = "_IMID_MERCHANT";
    protected $mer_key = "_MER_KEY_MERCHANT";
    // Constant
    protected $pay_method = "06";
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
     * generate Payloan
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
        $sellers = $this->generateSellers();

        $body = [
            "timeStamp" => $timestamp,
            "iMid" => $this->imid,
            "payMethod" => $this->pay_method,
            "mitraCd" => "KDVI",
            "currency" => "IDR",
            "amt" => $this->amt,
            "referenceNo" => $reference_no,
            "merchantToken" => $merchant_token,
            "dbProcessUrl" => "https://webhook.site/90aa57b4-9bdd-4f3c-bf0a-ca35d78897b0",
            "goodsNm" => "Goods",
            "cartData" => $cartData,
            "billingNm" => "John Test",
            "billingPhone" => "",
            "billingEmail" => "omen@example.com",
            "billingAddr" => "Jln. Raya Kasablanka Kav.88",
            "billingCity" => "South Jakarta",
            "billingState" => "DKI Jakarta",
            "billingPostCd" => "15119",
            "billingCountry" => "Indonesia",
            "deliveryNm" => "Hantu Kesiangan",
            "deliveryPhone" => "0812312312",
            "deliveryAddr" => "Jln.Puri Indah A3 No.3",
            "deliveryCity" => "West Jakarta",
            "deliveryState" => "DKI Jakarta",
            "deliveryPostCd" => "12345",
            "deliveryCountry" => "Indonesia",
            "userIP" => "127.0.0.1",
            "reqDt" => "",
            "reqTm" => "",
            "sellers" => $sellers,
            "instmntType" => "1",
            "instmntMon" => "1",
            "recurrOpt" => "",
            "tokenizeUser" => "2",
            "clientUserKey" =>"harfa.thandila@nicepay.co.id",
            "userToken" =>"5a291927-2089-4fdc-816d-85307a281931"
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
        $txid = "IONPAYTEST03202506291818301042";           // TODO : Fill with the registered transaction
        $reference_no = "RefactorOmen56477";                // TODO : Fill with the registered transaction
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
        $txid = "TNICEEW05105202506291742429168";
        $timestamp = Carbon::now()->format("YmdHis");
        $reference_no = "RefactorOmen19673";
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

    public function generateSellers()
    {
        return json_encode([
            "count" => "1",
            "item" => [
                [
                    "sellersId" => "SEL123",
                    "sellersNm" => "Sellers1",
                    "sellersEmail" => "sellers@test.com",
                    "sellersUrl" => "http://nicestore.store",
                    "sellersAddress" => [
                        "sellerNm" => "Sellers",
                        "sellerLastNm" => "1",
                        "sellerAddr" => "jalanberbangsa1",
                        "sellerCity" => "JakartaBarat",
                        "sellerPostCd" => "12344",
                        "sellerPhone" => "08123456789",
                        "sellerCountry" => "ID"
                    ]
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
