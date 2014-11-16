<?php
/**
 * simple.php
 * Created by Tim Whidden
 * @var array $config loaded via `../config.php`
 */
require_once '../config.php'; // consumerKey & consumerSecret
require_once '../vendor/autoload.php'; // composer autoloader

use TwitterAppAuth\TwitterAppAuth;

$twitterAppAuth   = new TwitterAppAuth('twhid.com/1.0');
$bearerTokenCache = '../bearerToken';
$consumerToken    = TwitterAppAuth::generateConsumerToken($config['consumerKey'], $config['consumerSecret']);

// TwitterAppAuth offers a basic disk-based caching mechanism
if (!$bearerToken = TwitterAppAuth::getCachedFile($bearerTokenCache)) {
    $bearerToken = $twitterAppAuth->generateBearerToken($consumerToken);
    @file_put_contents($bearerTokenCache, $bearerToken);
}

// get @twhid's last 3 tweets
echo $twitterAppAuth->getUserTimeline($bearerToken, array(
    'screen_name' => 'twhid',
    'count'       => 3,
    'trim_user'   => TRUE
));
