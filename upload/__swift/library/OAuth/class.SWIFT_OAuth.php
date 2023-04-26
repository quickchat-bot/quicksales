<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Nicolas Grondin
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2022, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

/**
 * The File Compressor
 *
 * @author Nicolas Grondin
 */
class SWIFT_OAuth extends SWIFT_Library
{
    public static function exchangeCode($_tokenURL, $_clientId, $_clientSecret, $_redirectURI, $_authCode) 
    {
        $_postData = array(
            "code" => $_authCode,
            "client_id" => $_clientId,
            "client_secret" => $_clientSecret,
            "redirect_uri" => $_redirectURI,
            "grant_type" => "authorization_code"
        );
        $_urlEncodedPostData = http_build_query($_postData);
        $_curlHandle = curl_init();
        curl_setopt($_curlHandle, CURLOPT_URL, $_tokenURL);
        curl_setopt($_curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($_curlHandle, CURLOPT_POST, true);
        curl_setopt($_curlHandle, CURLOPT_POSTFIELDS, $_urlEncodedPostData);
        $_response = curl_exec($_curlHandle);
        curl_close($_curlHandle);
        $_tokens = json_decode($_response, true);
        return $_tokens;
    }


    public static function refreshToken($_tokenURL, $_clientId, $_clientSecret, $_refreshToken) 
    {
        $_postData = array(
            "refresh_token" => $_refreshToken,
            "client_id" => $_clientId,
            "client_secret" => $_clientSecret,
            "grant_type" => "refresh_token"
        );
        $_urlEncodedPostData = http_build_query($_postData);
        $_curlHandle = curl_init();
        curl_setopt($_curlHandle, CURLOPT_URL, $_tokenURL);
        curl_setopt($_curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($_curlHandle, CURLOPT_POST, true);
        curl_setopt($_curlHandle, CURLOPT_POSTFIELDS, $_urlEncodedPostData);
        $_response = curl_exec($_curlHandle);
        curl_close($_curlHandle);
        $_tokens = json_decode($_response, true);
        return $_tokens;
    }
}