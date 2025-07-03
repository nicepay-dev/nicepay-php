<?php

namespace App\Http\Controllers\api\va;

use App\Http\Controllers\Controller;
use App\Models\Helper\HttpUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

use App\Models\Helper\Helpers;
use Carbon\Carbon;
use Throwable;

class DeleteVirtualAccountController extends Controller
{
    // Delete VA Endpoint
    protected $useProd = false;
    protected $useCloud = false;
    protected $delete_va_endpoint = "/api/v1.0/transfer-va/delete-va";

    // Credential
    protected $client_id = "CLIENT_KEY_MERCHANT";
    PROTECTED $key = "_PRIVATE_KEY_MERCHANT"; // string private key

    PROTECTED $client_secret = "_CLIENT_SECRET_MERCHANT"; // string CLIENT SECRET
    PROTECTED $access_token = "_ACCESS_TOKEN_MERCHANT";

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * delete virtual account
     *
     * @return JsonResponse
     */

    public function deleteVirtualAccount(): JsonResponse
    {
        $url = HttpUtil::getNicepayDomain($this->useProd, $this->useCloud) . "nicepay/" . $this->delete_va_endpoint;
        $helper = new Helpers();
        $http_method = "DELETE";
        $date = Carbon::now();
        $x_time_stamp = $date->toIso8601String();
        $time_stamp = $date->format("YmdHis");
        $partner_id = $this->client_id; // String partner id / merchantId
        $client_secret = $this->client_secret;

        $access_token = $this->access_token;

        $external_id = "MrVATst" . $time_stamp . Str::random(5);

        $totalAmount = [
            "value" => "15000.00",
            "currency" => "IDR"
        ];

        $additionalInfo = [
            "totalAmount" => $totalAmount,
            "tXidVA" => "TNICEVA02302202408021409400767",
            "cancelMessage" => "Cancel Virtual Account"
        ];

        $body = [
            "partnerServiceId" => "1234567",
            "customerNo" => "",
            "virtualAccountNo" => "9912304000008867",
            "trxId" => "trxIdVa20240802140941",
            "additionalInfo" => $additionalInfo,
        ];

        $string_to_sign = $helper->generateStringToSign(
            $http_method,
            $this->delete_va_endpoint,
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
            $response = HttpUtil::deleteJsonRequestWithHeader($url, $body, $header);

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
