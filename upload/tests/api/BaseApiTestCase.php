<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
 * _______________________________________________
 *
 * @author        Werner Garcia <werner.garcia@crossover.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://opencart.com.vn/license
 * @link          http://opencart.com.vn
 *
 * ###############################################
 */

namespace Tests\Api;

require_once dirname(__DIR__) . '/../__swift/config/config.php';

/**
 * Class BaseApiTestCase
 */
abstract class BaseApiTestCase extends \PHPUnit\Framework\TestCase
{
    protected $http;
    protected static $_url = '';
    protected static $_params = [];

    public function setUp()
    {
        if (empty(static::$_url)) {
            $_SWIFT = \SWIFT::GetInstance();
            $apiEnabled = $_SWIFT->Settings->GetBool('g_enableapiinterface');
            if (!$apiEnabled) {
                $this->fail('API interface is not enabled in settings');
                return;
            }
            static::$_url = $_SWIFT->Settings->Get('general_producturl');
            static::$_url .= ((substr(static::$_url, -1) === '/') ? '' : '/') . 'api/index.php';
            $apiKey = $_SWIFT->Settings->GetKey('restapi', 'apikey');
            $secretKey = $_SWIFT->Settings->GetKey('restapi', 'secretkey');

            // We need to generate a random string of ten digits
            $salt = mt_rand();

            // And then compute the signature by hashing the salt with the secret key as the key
            $signature = hash_hmac('sha256', $salt, $secretKey, true);

            // Finally base64 encode...
            $encodedSignature = base64_encode($signature);

            static::$_params = [
                'apikey' => $apiKey,
                'salt' => $salt,
                'signature' => $encodedSignature,
            ];
        }

        $this->http = new \GuzzleHttp\Client();
    }

    public function tearDown()
    {
        $this->http = null;
    }

    /**
     * @param $endpoint
     * @param string $method
     * @param array $params
     * @param int $expectedStatusCode
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getResponse($endpoint, $method = 'GET', $params = [], $expectedStatusCode = 200)
    {
        if (in_array($method, ['POST', 'PUT'])) {
            $response = $this->http->request($method, static::$_url . '?e=' . $endpoint, [
                'form_params' => array_merge(static::$_params,
                    $params),
            ]);
        } else {
            $response = $this->http->request($method, static::$_url, [
                'query' => array_merge(static::$_params,
                    ['e' => $endpoint], $params),
            ]);
        }

        $this->assertEquals($expectedStatusCode, $response->getStatusCode());

        $contentType = $response->getHeaders()['Content-Type'][0];
        $this->assertContains('text/xml', $contentType);

        return $response;
    }

    /**
     * @param mixed|\Psr\Http\Message\ResponseInterface $response
     * @param int $options
     * @return mixed
     */
    public function getArrayFromResponse($response, $options = LIBXML_NOCDATA)
    {
        $str = $response->getBody()->getContents();
        $xml = simplexml_load_string($str, 'SimpleXMLElement', $options);
        $json = json_encode($xml);
        return json_decode($json, true);
    }

    /**
     * @param string $str
     * @param int $options
     * @return mixed
     */
    public function getArrayFromXml($str, $options = LIBXML_NOCDATA)
    {
        $xml = simplexml_load_string($str, 'SimpleXMLElement', $options);
        $json = json_encode($xml);
        return json_decode($json, true);
    }
}
