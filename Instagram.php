<?php

require_once 'Zend/Http/Client.php';

class Instagram {

    /**
     * The name of the GET param that holds the authentication code
     * @var string
     */
    const RESPONSE_CODE_PARAM = 'code';

    /**
     * Format for endpoint URL requests
     * @var string
     */
    protected $_endpointUrls = array(
        'user' => 'https://api.instagram.com/v1/users/%d/?access_token=%s',
        'user_feed' => 'https://api.instagram.com/v1/users/self/feed?access_token=%s&max_id=%d&min_id=%d',
        'user_recent' => 'https://api.instagram.com/v1/users/%d/media/recent/?access_token=%s&max_id=%d&min_id=%d&max_timestamp=%d&min_timestamp=%d',
        'user_search' => 'https://api.instagram.com/v1/users/search?q=%s&access_token=%s',
        'user_follows' => 'https://api.instagram.com/v1/users/%d/follows?access_token=%s',
        'user_followed_by' => 'https://api.instagram.com/v1/users/%d/followed-by?access_token=%s',
        'user_requested_by' => 'https://api.instagram.com/v1/users/self/requested-by?access_token=%s',
    );

    /**
    * Configuration parameter
    */
    protected $_config = array();

    /**
     * Access token
     * @var string
     */
    protected $_accessToken = null;

    /**
     * Holds the HTTP client instance
     * @param Zend_Http_Client $httpClient
     */
    protected $_httpClient = null;

    public function __construct($config = null) {
        $this->_config = $config;
        if (empty($config)) {
            throw new InstagramException('Configuration params are empty or not an array.');
        }
    }

    protected function _init() {
        // Requests the OAuth token if none passed 
        if ($this->_accessToken == null) {
            $this->_accessToken = json_decode($this->_getOauthToken())->access_token;
        }
    }

    /**
     * Instantiates the internal HTTP client
     * @param string $url
     * @param string $method
     */
    protected function _initHttpClient($url, $method = Zend_Http_Client::GET) {
        $this->_httpClient = new Zend_Http_Client($url);
        $this->_httpClient->setMethod($method);
    }

    /**
     * Sets a post param to be setn along with the client request
     * @param string $name
     * @param mixed $value
     */
    protected function _setHttpClientPostParam($name, $value) {
        $this->_httpClient->setParameterPost($name, $value);
    }

    /**
     * Returns the body of the HTTP client response
     * @return string
     */
    protected function _getHttpClientResponse() {
        return $this->_httpClient->request()->getBody();
    }

    /**
     * Gets the code param received during the authorization step
     */
    public function getCode() {
        return $_GET[self::RESPONSE_CODE_PARAM];
    }

    /**
     * Retrieves the OAuth token to be used in every request
     * @return string
     */
    protected function _getOauthToken() {
        $this->_initHttpClient($this->_config['site_url'], Zend_Http_Client::POST);
        $this->_setHttpClientPostParam('client_id', $this->_config['client_id']);
        $this->_setHttpClientPostParam('client_secret', $this->_config['client_secret']);
        $this->_setHttpClientPostParam('grant_type', $this->_config['grant_type']);
        $this->_setHttpClientPostParam('redirect_uri', $this->_config['redirect_uri']);
        $this->_setHttpClientPostParam('code', $this->getCode());

        return $this->_getHttpClientResponse();
    }

     /**
      * Get basic information about a user.
      * @param $id
      * @param $oauthToken
      */
    public function getUser($id) {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['user'], $id, $this->_accessToken);
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * See the authenticated user's feed.
     * @param integer $maxId. Return media after this maxId.
     * @param integer $minId. Return media before this minId.
     */
    public function getUserFeed($maxId = null, $minId = null) {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['user_feed'], $this->_accessToken, $maxId, $minId);
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Get the most recent media published by a user.
     * @param $id. User id
     * @param $maxId. Return media after this maxId
     * @param $minId. Return media before this minId
     * @param $maxTimestamp. Return media before this UNIX timestamp
     * @param $minTimestamp. Return media after this UNIX timestamp
     */
    public function getUserRecent($id, $maxId = '', $minId = '', $maxTimestamp = '', $minTimestamp = '') {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['user_recent'], $id, $this->_accessToken, $maxId, $minId, $maxTimestamp, $minTimestamp);
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Search for a user by name.
     * @param string $name. A query string
     */
    public function searchUser($name) {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['user_search'], $name, $this->_accessToken);
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Get the list of users this user follows.
     * @param integer $id. The user id
     */
    public function getUserFollows($id) {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['user_follows'], $id, $this->_accessToken);
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Get the list of users this user is followed by.
     * @param integer $id
     */
    public function getUserFollowedBy($id) {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['user_followed_by'], $id, $this->_accessToken);
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * List the users who have requested this user's permission to follow
     */
    public function getUserRequestedBy() {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['user_requested_by'], $this->_accessToken);
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }
}

class InstagramException extends Exception {
}
