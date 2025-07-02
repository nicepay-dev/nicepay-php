<?php

namespace App\Models\Helper;

use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

use Carbon\Carbon;
use Throwable;

class HttpUtil
{
    /**
     * Used to define the nicepay domain (based on useCloud and useProd)
     *
     * @var Array<String, String>
     */
    public static function getNicepayDomain(bool $useProd, bool $useCloud): string
    {
        if ($useCloud) {
            return $useProd
                ? "https://services.nicepay.co.id"
                : "https://dev-services.nicepay.co.id";
        } else {
            return $useProd
                ? "https://www.nicepay.co.id"
                : "https://dev.nicepay.co.id";
        }
    }

    /**
     * Call POST API with x-www-form-urlencoded (Form request)
     *
     * @param $url string
     * @param $params array
     */
    public static function postFormRequest($url, $params): Response
    {
        // Print debug info
        print_r("===========\n");
        print_r("= Request =\n");
        print_r("===========\n");
        print_r("Url    : " . $url . "\n");
        print_r("Body   : " . http_build_query($params) . "\n");

        // Send POST request as x-www-form-urlencoded
        $response = Http::asForm()->post($url, $params);

        // Print response
        print_r("===========\n");
        print_r("= Response =\n");
        print_r("===========\n");
        print_r($response->body());


        print_r("API CALL END\n");
        print_r("\n\n============\n\n");

        return $response;
    }

    /**
     * Call POST API with JSON request body
     *
     * @param $url string
     * @param $body array
     */
    public static function postJsonRequest($url, $body): Response
    {
        // Print URL
        print_r("===========\n");
        print_r("= Request =\n");
        print_r("===========\n");
        print_r("Url    : " . $url . "\n");
        print_r("Body   : " . json_encode($body) . "\n");

        // Send request
        $response = Http::post($url, $body);

        // Print status and response body
        print_r("============\n");
        print_r("= Response =\n");
        print_r("============\n");
        print_r("Status : " . $response->status() . "\n");
        print_r("Body   :\n" . $response->body() . "\n\n");

        print_r("API CALL END\n");
        print_r("============\n\n");

        return $response;
    }

    public static function postJsonRequestWithHeader($url, $body, $header): Response
    {
        // Print URL
        print_r("===========\n");
        print_r("= Request =\n");
        print_r("===========\n");
        print_r("Url    : " . $url . "\n");
        print_r("Body   : " . json_encode($body) . "\n");

        // Send request
        $response = Http::withHeaders($header)->post($url, $body);

        // Print status and response body
        print_r("============\n");
        print_r("= Response =\n");
        print_r("============\n");
        print_r("Status : " . $response->status() . "\n");
        print_r("Body   :\n" . $response->body() . "\n\n");

        print_r("API CALL END\n");
        print_r("============\n\n");

        return $response;
    }

     public static function deleteJsonRequestWithHeader($url, $body, $header): Response
    {
        // Print URL
        print_r("===========\n");
        print_r("= Request =\n");
        print_r("===========\n");
        print_r("Url    : " . $url . "\n");
        print_r("Body   : " . json_encode($body) . "\n");

        // Send request
        $response = Http::withHeaders($header)->delete($url, $body);

        // Print status and response body
        print_r("============\n");
        print_r("= Response =\n");
        print_r("============\n");
        print_r("Status : " . $response->status() . "\n");
        print_r("Body   :\n" . $response->body() . "\n\n");

        print_r("API CALL END\n");
        print_r("============\n\n");

        return $response;
    }

    /**
     * Redirect the browser into defined target url
     *
     * @param $url string
     * @param $body array
     */
    public static function redirectWithQueryParams($url, $params)
    {
        // Print original
        print_r("===========\n");
        print_r("= Request =\n");
        print_r("===========\n");
        print_r("Url    : " . $url . "\n");
        print_r("Body   : " . json_encode($params) . "\n");

        // Separate callbackUrl to keep it raw
        $callbackUrl = $params['callBackUrl'];
        unset($params['callBackUrl']);

        // Build query string except callbackUrl
        $query = http_build_query($params);

        // Append callbackUrl as-is (not encoded)
        $fullUrl = $url . '?' . $query . '&callBackUrl=' . $callbackUrl;

        // Print for debug
        print_r("Redirecting to: $fullUrl\n");

        // Uncomment to actually redirect
        header("Location: $fullUrl");
        exit;
    }

    public static function generateSuccessRedirectResponse(): JsonResponse
    {
        return response()->json([
            'status' => 200,
            'message' => "Redirected to browser"
        ]);
    }

    public static function generateErrorResponse(Throwable $th)
    {
        print_r("Exception : " . $th);

        return response()->json([
            'status' => 500,
            'message' => "Internal Server Error",
            'data' => $th
        ]);
    }
}
