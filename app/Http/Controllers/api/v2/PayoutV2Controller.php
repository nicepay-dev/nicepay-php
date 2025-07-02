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

class PayoutV2Controller extends Controller
{
    // Payout Endpoint
    protected $useProd = false;
    protected $useCloud = false;
    protected $registration_endpoint = "/nicepay/api/direct/v2/requestPayout";
    protected $inquiry_endpoint = "/nicepay/api/direct/v2/inquiryPayout";
    protected $cancel_endpoint = "/nicepay/api/direct/v2/cancelPayout";
    protected $approve_endpoint = "/nicepay/api/direct/v2/approvePayout";
    protected $reject_endpoint ="/nicepay/api/direct/v2/rejectPayout";
    protected $balance_endpoint ="/nicepay/api/direct/v2/balanceInquiry";

    // Credential CC
    protected $imid = "TNICEPO071";
    protected $mer_key = "33F49GnCMS1mFYlGXisbUDzVf2ATWCl9k3R++d5hDd3Frmuos/XLx8XhXpe+LDYAbpGKZYSwtlyyLOtS/8aD7A==";

    // Constant
    protected $payoutMethod = "1";
    protected $amt = "15000";
    protected $accountNo ="5345000060";
    protected $bankCd = "CENA";



    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * generate Credit Card
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
        $bankCd = $this->bankCd;
        $accountNo = $this->accountNo;
        $merchant_token = $this->generateMerchantToken($timestamp, $this->imid, $amt, $accountNo, $this->mer_key);

        $body = [
            "timeStamp" => $timestamp,
            "iMid" => $this->imid,
            "payoutMethod" => $this->payoutMethod,
            "accountNo" => $this->accountNo,
            "amt" => $this->amt,
            "referenceNo" => $reference_no,
            "merchantToken" => $merchant_token,
            "benefNm" => "PT IONPAY NETWORKS",
            "benefStatus" => "1",
            "benefType" => "1",
            "bankCd" => $bankCd,
            "benefPhone" => "01234567891012131415",
            "description" => "This is test",
            "msId" => "",
            "reservedDt" => "20250703",
            "reservedTm" => "120000"
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

    public function approve(): JsonResponse
    {
        $url = HttpUtil::getNicepayDomain($this->useProd, $this->useCloud) . $this->approve_endpoint;

        // Request body parameter
        $txid = "TNICEPO07107202507022154308613";
        $timestamp = Carbon::now()->format("YmdHis");
        $merchant_token = $this->generateMerchantTokenBytXid($timestamp, $this->imid, $txid, $this->mer_key);

        $body = [
            "timeStamp" => $timestamp,
            "iMid" => $this->imid,
            "merchantToken" => $merchant_token,
            "tXid" => $txid,
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
        $txid = "TNICEPO07107202507022006092969";           // TODO : Fill with the registered transaction
        $accountNo = $this->accountNo;
        $merchant_token = $this->generateMerchantTokenInquiry($timestamp, $this->imid, $txid, $accountNo, $this->mer_key);

        $body = [
            "iMid" => $this->imid,
            "timeStamp" => $timestamp,
            "merchantToken" => $merchant_token,
            "accountNo" => $accountNo,
            "tXid" => $txid,
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

        // Request body parameter
        $txid = "TNICEPO07107202507022148458298";
        $timestamp = Carbon::now()->format("YmdHis");
        $merchant_token = $this->generateMerchantTokenBytXid($timestamp, $this->imid, $txid, $this->mer_key);

        $body = [
            "timeStamp" => $timestamp,
            "iMid" => $this->imid,
            "merchantToken" => $merchant_token,
            "tXid" => $txid,
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

    public function reject(): JsonResponse
    {
        $url = HttpUtil::getNicepayDomain($this->useProd, $this->useCloud) . $this->reject_endpoint;

        // Request body parameter
        $txid = "TNICEPO07107202507022154308613";
        $timestamp = Carbon::now()->format("YmdHis");
        $merchant_token = $this->generateMerchantTokenBytXid($timestamp, $this->imid, $txid, $this->mer_key);

        $body = [
            "timeStamp" => $timestamp,
            "iMid" => $this->imid,
            "merchantToken" => $merchant_token,
            "tXid" => $txid,
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

    public function balance(): JsonResponse
    {
        $url = HttpUtil::getNicepayDomain($this->useProd, $this->useCloud) . $this->balance_endpoint;

        // Request body parameter
        $timestamp = Carbon::now()->format("YmdHis");
        $merchant_token = $this->generateMerchantTokenforBalance($timestamp, $this->imid, $this->mer_key);

        $body = [
            "timeStamp" => $timestamp,
            "iMid" => $this->imid,
            "merchantToken" => $merchant_token,
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

    private function generateMerchantToken($timestamp, $imid, $amt, $accountNo, $mer_key)
    {
        $merchant_data = $timestamp . $imid . $amt . $accountNo . $mer_key;
        return hash("sha256", $merchant_data);
    }

    private function generateMerchantTokenInquiry($timestamp, $imid, $txid, $accountNo, $mer_key)
    {
        $merchant_data = $timestamp . $imid . $txid . $accountNo . $mer_key;
        return hash("sha256", $merchant_data);
    }

    private function generateMerchantTokenBytXid($timestamp, $imid, $txid, $mer_key)
    {
        $merchant_data = $timestamp . $imid . $txid . $mer_key;
        return hash("sha256", $merchant_data);
    }

    private function generateMerchantTokenforBalance($timestamp, $imid, $mer_key)
    {
        $merchant_data = $timestamp . $imid . $mer_key;
        return hash("sha256", $merchant_data);
    }
}
