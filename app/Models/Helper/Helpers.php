<?php

namespace App\Models\Helper;

use App\Http\Controllers\api\accessToken\GenerateAccessTokenController;
use Illuminate\Support\Str;

use Carbon\Carbon;

class Helpers
{
    /**
     * The attributes that are mass assignable.
     *
     * @var Array<String, String>
     */
    protected $fillable_header = [
        'Content-Type',
        'X-TIMESTAMP',
        'X-CLIENT-KEY',
        'X-SIGNATURE',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string, string>
     */
    protected $fillable_body = [
        'grantType',
        'additionalInfo'
    ];

    /**
     * generate key
     * @param String $key
     *
     * @return String $key
     */
    function generateKey($key)
    {
        return "-----BEGIN RSA PRIVATE KEY-----" . "\r\n" .
        $key .
        "\r\n" .
        "-----END RSA PRIVATE KEY-----";
    }

    /**
     * generate header
     *
     * @param String $timestamp yyyy-MM-ddTHH:mm:ss.SSSTZD
     * @param String $client_key kredential
     * @param String $signature
     *
     * @return array<string, string> header
     */
    function generateHeaderAccessToken($timestamp, $client_key, $signature)
    {
        return  [
            "Content-Type" => "Application/Json",
            "X-TIMESTAMP" => $timestamp,
            "X-CLIENT-KEY" => $client_key,
            "X-SIGNATURE" => $signature
        ];
    }

    /**
     * function generateHeader
     * using for generate header
     *
     * @param String $authorization string access token (generate from hit acess token)
     * @param String $timestamp yyyy-MM-ddTHH:mm:ss.SSSTZD
     * @param String $signature
     * @param String $partner_id
     * @param String $external_id (unique value for every transaction)
     * @param String $channel_id
     *
     * @return Array<String, String> header
     */
    function generateHeader(
        $authorization,
        $timestamp,
        $signature,
        $partner_id,
        $external_id,
        $channel_id
        )
    {
        return  [
            "Content-Type" => "Application/Json",
            "Authorization" => "Bearer " . $authorization,
            "X-TIMESTAMP" => $timestamp,
            "X-SIGNATURE" => $signature,
            "X-PARTNER-ID" => $partner_id,
            "X-EXTERNAL-ID" => $external_id,
            "CHANNEL-ID" => $channel_id
        ];
    }

    /**
     * function generateSignature
     * encrypt using input method like SHA 256 with private key
     * generate key using openssl, get key with pem format
     * encode sign using base64_encode
     *
     * @param $string_to_sign string
     * @param $private_key string
     * @param method constant method generate signature
     *
     * @return String Signature
     */
    function generateSignature($string_to_sign, $private_key, $method)
    {
        $private_key = openssl_pkey_get_private($private_key);

        openssl_sign($string_to_sign, $signature, $private_key, $method);

        openssl_pkey_free($private_key);

        $signature = base64_encode($signature);

        return $signature;
    }

    /**
     * function generateStringToSign
     * create payload string for sign to generate signature
     *
     * @param $http_method string
     * @param $url string
     * @param $access_token string
     * @param $body string
     * @param $time_stamp ISO8601 format
     * @param method constant string
     *
     * @return String payload
     */
    function generateStringToSign($http_method, $url, $access_token, $body, $time_stamp)
    {
        $string_body = json_encode($body);
        $hash_body = Helpers::sha256EncodeHex($string_body);
        $string_to_sign = $http_method . ":" . $url . ":" . $access_token . ":" . $hash_body . ":" . $time_stamp;

        return $http_method . ":" . $url . ":" . $access_token . ":" . $hash_body . ":" . $time_stamp;
    }

    /**
     * encrypt SHA 256 with private key
     *
     * @param $body string
     *
     * @return String hmac
     */
    function sha256EncodeHex($body)
    {
        $hmac = hash('sha256', $body);

        return Str::lower($hmac);
    }

    /**
     * function hmacSHA512Encoded
     * encrypt HMAC SHA 512  with private key
     * encode hash using base64
     *
     * @param $string_to_sign string
     * @param $client_secret string
     *
     * @return String Signature
     */
    function hmacSHA512Encoded($string_to_sign, $client_secret)
    {

        $hash = hash_hmac("sha512", $string_to_sign, $client_secret, true);

        return base64_encode($hash);
    }

    public function generateAccessToken($client_id, $key)
    {
        print_r("===========================\n");
        print_r("Generate Access Token Start\n");
        print_r("===========================\n");
        $accessTokenService = new GenerateAccessTokenController();
        $accessToken =  $accessTokenService->generateAccessTokenWithParam($client_id, $key);
        print_r("Access Token : " . $accessToken . "\n");
        print_r("===========================\n");
        print_r("Generate Access Token End\n");
        print_r("===========================\n\n\n");
        return $accessToken;
    }
}
