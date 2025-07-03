<?php

namespace App\Http\Controllers\api\v2;

use App\Http\Controllers\Controller;
use App\Models\Helper\HttpUtil;
use Illuminate\Http\JsonResponse;
use Throwable;

class FixOpenV2Controller extends Controller
{
    // VA Endpoint
    protected $useProd = false;
    protected $useCloud = false;
    protected $registration_cust_endpoint = "/nicepay/api/vacctCustomerRegist.do";
    protected $inquiry_cust_endpoint = "/nicepay/api/vacctCustomerInquiry.do";
    protected $deposit_inquiry_endpoint = "/nicepay/api/vacctInquiry.do";
    protected $cust_update_endpoint = "/nicepay/api/vacctCustomerUpdate.do";

    // Credential
    protected $imid = "_IMID_MERCHANT";
    protected $mer_key = "_MER_KEY_MERCHANT";

    protected $customer_nm = "TESTING";

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * generate fix open virtual account
     *
     * @return JsonResponse
     */
    public function vacctCustomerRegist(): JsonResponse
    {
        // Endpoint
        $url = HttpUtil::getNicepayDomain($this->useProd, $this->useCloud) . $this->registration_cust_endpoint;

        // Request body parameter
        $customer_id = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        $merchant_token = $this->generateMerchantToken($this->imid, $customer_id, $this->mer_key);

        $body = [
            "iMid" => $this->imid,
            "customerId" => $customer_id,
            "customerNm" => $this->customer_nm,
            "merchantToken" => $merchant_token,
            "vacctValidDt" => "",
            "vacctValidTm" => "",

        ];

        try {
            $response = HttpUtil::postFormRequest($url, $body);

            return response()->json([
                'status' => $response->status(),
                'data' => $response->json()
            ]);
        } catch (Throwable $th) {
            return HttpUtil::generateErrorResponse($th);
        }
    }

    public function vacctCustomerInquiry(): JsonResponse
    {
        // Endpoint
        $url = HttpUtil::getNicepayDomain($this->useProd, $this->useCloud) . $this->inquiry_cust_endpoint;

        // Request body parameter
        $customer_id = "23092130";
        $merchant_token = $this->generateMerchantToken($this->imid, $customer_id, $this->mer_key);

        $body = [
            "iMid" => $this->imid,
            "customerId" => $customer_id,
            "merchantToken" => $merchant_token,
        ];
        try {
            $response = HttpUtil::postFormRequest($url, $body);

            return response()->json([
                'status' => $response->status(),
                'data' => $response->json()
            ]);
        } catch (Throwable $th) {
            return HttpUtil::generateErrorResponse($th);
        }
    }

    public function vacctDepositInquiry(): JsonResponse
    {   $url = HttpUtil::getNicepayDomain($this->useProd, $this->useCloud) . $this->deposit_inquiry_endpoint;

        //request body parameter
        $vacct_no="7007216123092130";
        $start_dt="20250701";
        $end_dt="20250701";
        $merchant_token = $this->generateMerchantForDeposit($this->imid, $vacct_no, $start_dt, $this->mer_key);

        $body = [
            "iMid" => $this->imid,
            "vacctNo" => $vacct_no,
            "startDt" => $start_dt,
            "endDt" => $end_dt,
            "merchantToken" => $merchant_token,
        ];
        try {
            $response = HttpUtil::postFormRequest($url, $body);

            return response()->json([
                'status' => $response->status(),
                'data' => $response->json()
            ]);
        } catch (Throwable $th) {
            return HttpUtil::generateErrorResponse($th);
        }
    }

    /**
     * Update data for fix open virtual account <br><br>
     * Update Type :
     *  - 1 : for Registration new VA Number Depends On Bank Not Registered Yet
     *  - 2 : For Update The Customer Name
     *  - 3 : For Delete VA Number (Expire VA)
     */

    public function vacctCustomerUpdate(): JsonResponse
    {
        $url = HttpUtil::getNicepayDomain($this->useProd, $this->useCloud) . $this->cust_update_endpoint;

        //request body parameter
        $update_type = "2";
        $customer_id = "23725837";
        $merchant_token = $this->generateMerchantToken($this->imid, $customer_id, $this->mer_key);

        $body = [
            "iMid" => $this->imid,
            "customerId" => $customer_id,
            "customerNm" => "Parhan",
            "merchantToken" => $merchant_token,
            "updateType" => $update_type,
        ];

        try {
            $response = HttpUtil::postFormRequest($url, $body);

            return response()->json([
                'status' => $response->status(),
                'data' => $response->json()
            ]);
        } catch (Throwable $th) {
            return HttpUtil::generateErrorResponse($th);
        }
    }

    private function generateMerchantToken($imid, $customer_id , $mer_key)
    {
        $merchant_data = $imid . $customer_id . $mer_key;
        return hash("sha256", $merchant_data);
    }

    private function generateMerchantForDeposit($imid, $vacct_no, $startDt , $mer_key)
    {
        $merchant_data = $imid . $vacct_no . $startDt . $mer_key;
        return hash("sha256", $merchant_data);
    }

}
