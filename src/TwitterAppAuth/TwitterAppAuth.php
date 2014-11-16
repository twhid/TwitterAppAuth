<?php
/**
 * TwitterAppAuth.php
 * Created by Tim Whidden
 *
 * Twitter documentation for app auth as of Mar 29, 2015 allows you to:
 * * Pull user timelines;
 * * Access friends and followers of any account;
 * * Access lists resources;
 * * Search in tweets;
 * * Retrieve any user information;
 */

namespace TwitterAppAuth;

use \Requests;

class TwitterAppAuth
{
    private $userAgent = 'TwitterAppAuth/0.0.1';
    private $headers = array();

    /**
     * @param null $userAgent
     */
    public function __construct($userAgent = NULL) {
        if (!is_null($userAgent)) {
            $this->userAgent = $userAgent;
        }
        $this->setHeaders(array('User-Agent' => $this->userAgent));
    }

    /**
     * @return string
     */
    public function getUserAgent() {
        return $this->userAgent;
    }

    /**
     * @return array
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * @description Common headers used for all requests
     * @param array $headers
     */
    public function setHeaders($headers) {
        $this->headers = $headers;
    }

    /**
     * @param string $endpoint
     * @param string $bearerToken
     * @param array $headers
     * @return \Requests_Response
     */
    private function authGet($endpoint, $bearerToken, $headers = array()) {
        $headers = array_merge($headers, array('Authorization' => 'Bearer ' . $bearerToken));
        return $this->request('get', $endpoint, $headers);
    }

    /**
     * @param string $consumerToken
     * @return array
     */
    private static function authPostHeaders($consumerToken) {
        return array(
            'Authorization' => 'Basic ' . $consumerToken,
            'Accept'        => '*/*',
            'Content-Type'  => 'application/x-www-form-urlencoded'
        );
    }

    /**
     * @param string $verb
     * @param string $path
     * @param array $headers
     * @param array $params
     * @return \Requests_Response
     */
    private function request($verb, $path, $headers = array(), $params = array()) {
        $url = Constants::API_HOST . $path;
        $headers = array_merge($this->headers, $headers);
        return call_user_func(array('Requests', $verb), $url, $headers, $params);
    }

    /**
     * @param $consumerKey
     * @param $consumerSecret
     * @return string
     */
    public static function generateConsumerToken($consumerKey, $consumerSecret) {
        $encodedConsumerKey    = urlencode($consumerKey);
        $encodedConsumerSecret = urlencode($consumerSecret);
        return base64_encode($encodedConsumerKey . ":" . $encodedConsumerSecret);
    }

    /**
     * @description A very simple caching strategy. It's probably OK for
     * low traffic sites. Or, extend this class and implement as you please.
     * @param string $path
     * @param int $cacheControl
     * @return bool|string
     */
    public static function getCachedFile($path, $cacheControl = 600) {
        if (file_exists($path) && filemtime($path) > time() - $cacheControl) {
            return file_get_contents($path);
        }
        return FALSE;
    }

    /**
     * @param string $consumerToken
     * @return string
     */
    public function generateBearerToken($consumerToken) {
        $headers = array_merge(self::authPostHeaders($consumerToken), array(
            'Content-Length' => strlen(Constants::GRANT_TYPE)
        ));
        $params  = explode('=', Constants::GRANT_TYPE);
        $params  = array($params[0] => $params[1]);
        $res     = $this->request('post', Constants::OAUTH2_TOKEN, $headers, $params);
        $body    = json_decode($res->body);
        return $body->token_type == 'bearer' ? $body->access_token : NULL;
    }

    /**
     * @param string $consumerToken
     * @param string $bearerToken
     * @return mixed
     */
    public function invalidateBearerToken($consumerToken, $bearerToken) {
        $headers = array_merge(self::authPostHeaders($consumerToken), array(
            'Content-Length' => strlen('access_token=' . $bearerToken)
        ));
        $params  = array('access_token' => $bearerToken);
        $res     = $this->request('post', Constants::OAUTH2_INVALIDATE_TOKEN, $headers, $params);
        return $res->body;
    }

    /**
     * @param array $options
     * @param string $accessToken
     * @return mixed
     * @throws \Exception
     */
    public function getUserTimeline($accessToken, $options = array()) {
        $url = Constants::USER_TIMELINE . '?' . http_build_query($options);
        $res = $this->authGet($url, $accessToken);
        return $res->body;
    }
}
