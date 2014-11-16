<?php
/**
 * Constants.php
 * Created by timwhidden
 * Date: 11/9/14
 */

namespace TwitterAppAuth;


class Constants
{
    const API_HOST = 'https://api.twitter.com/';
    const OAUTH2_TOKEN = "oauth2/token/";
    const OAUTH2_INVALIDATE_TOKEN = "oauth2/invalidate_token";
    const GRANT_TYPE = 'grant_type=client_credentials';
    const USER_TIMELINE = '1.1/statuses/user_timeline.json';
}
