<?php
/**
 * Instagram PHP implementation API
 * URLs: http://www.mauriciocuenca.com/
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
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
        'user_relationship' => 'https://api.instagram.com/v1/users/%d/relationship?access_token=%s',
        'modify_user_relationship' => 'https://api.instagram.com/v1/users/%d/relationship?action=%s&access_token=%s',
        'media' => 'https://api.instagram.com/v1/media/%d?access_token=%s',
        'media_search' => 'https://api.instagram.com/v1/media/search?lat=%s&lng=%s&max_timestamp=%d&min_timestamp=%d&distance=%d&access_token=%s',
        'media_popular' => 'https://api.instagram.com/v1/media/popular?access_token=%s',
        'media_comments' => 'https://api.instagram.com/v1/media/%d/comments?access_token=%s',
        'post_media_comment' => 'https://api.instagram.com/v1/media/%d/comments?access_token=%s',
        'delete_media_comment' => 'https://api.instagram.com/v1/media/%d/comments?comment_id=%d&access_token=%s',
        'likes' => 'https://api.instagram.com/v1/media/%d/likes?access_token=%s',
        'post_like' => 'https://api.instagram.com/v1/media/%d/likes,',
        'remove_like' => 'https://api.instagram.com/v1/media/%d/likes?access_token=%s',
        'tags' => 'https://api.instagram.com/v1/tags/%s?access_token=%s',
        'tags_recent' => 'https://api.instagram.com/v1/tags/%s/media/recent?max_id=%d&min_id=%d&access_token=%s',
        'tags_search' => 'https://api.instagram.com/v1/tags/search?q=%s&access_token=%s',
        'locations' => 'https://api.instagram.com/v1/locations/%d?access_token=%s',
        'locations_recent' => 'https://api.instagram.com/v1/locations/%d/media/recent/?max_id=%d&min_id=%d&max_timestamp=%d&min_timestamp=%d&access_token=%s',
        'locations_search' => 'https://api.instagram.com/v1/locations/search?lat=%s&lng=%s&foursquare_id=%d&distance=%d&access_token=%s',
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

    /**
     * Constructor needs to receive the config as an array
     * @param mixed $config
     */
    public function __construct($config = null) {
        $this->_config = $config;
        if (empty($config)) {
            throw new InstagramException('Configuration params are empty or not an array.');
        }
    }

    /**
     * The init method is triggered on every call to retrieve the
     * access token in case is not in the current instance.
     */
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

    /**
     * Get information about the current user's relationship (follow/following/etc) to another user.
     * @param integer $id
     */
    public function getUserRelationship($id) {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['user_relationship'], $id, $this->_accessToken);
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Modify the relationship between the current user and the target user
     * In order to perform this action the scope must be set to 'relationships'
     * @param integer $id
     * @param string $action. One of follow/unfollow/block/unblock/approve/deny
     */
    public function modifyUserRelationship($id, $action) {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['modify_user_relationship'], $id, $action, $this->_accessToken);
        $this->_initHttpClient($endpointUrl, Zend_Http_Client::POST);
        return $this->_getHttpClientResponse();
    }

    /**
     * Get information about a media object.
     * @param integer $mediaId
     */
    public function getMedia($id) {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['media'], $id, $this->_accessToken);
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Search for media in a given area.
     * @param float $lat
     * @param float $lng
     * @param integer $maxTimestamp
     * @param integer $minTimestamp
     * @param integer $distance
     */
    public function mediaSearch($lat, $lng, $maxTimestamp = '', $minTimestamp = '', $distance = '') {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['media_search'], $lat, $lng, $maxTimestamp, $minTimestamp, $distance, $this->_accessToken);
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Get a list of what media is most popular at the moment.
     */
    public function getPopularMedia() {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['media_popular'], $this->_accessToken);
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Get a full list of comments on a media.
     * @param integer $id
     */
    public function getMediaComments($id) {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['media_comments'], $id, $this->_accessToken);
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Create a comment on a media.
     * @param integer $id
     * @param string $text
     */
    public function postMediaComment($id, $text) {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['post_media_comment'], $id, $text, $this->_accessToken);
        $this->_initHttpClient($endpointUrl, Zend_Http_Client::POST);
        return $this->_getHttpClientResponse();
    }

    /**
     * Remove a comment either on the authenticated user's media or authored by the authenticated user.
     * @param integer $mediaId
     * @param integer $commentId
     */
    public function deleteComment($mediaId, $commentId) {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['delete_media_comment'], $mediaId, $commentId, $this->_accessToken);
        $this->_initHttpClient($endpointUrl, Zend_Http_Client::DELETE);
        return $this->_getHttpClientResponse();
    }

    /**
     * Get a list of users who have liked this media.
     * @param integer $mediaId
     */
    public function getLikes($mediaId) {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['likes'], $mediaId, $this->_accessToken);
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Set a like on this media by the currently authenticated user.
     * @param integer $mediaId
     */
    public function postLike($mediaId) {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['post_like'], $mediaId);
        $this->_initHttpClient($endpointUrl, Zend_Http_Client::POST);
        $this->_setHttpClientPostParam('access_token', $this->_accessToken);
        return $this->_getHttpClientResponse();
    }

    /**
     * Remove a like on this media by the currently authenticated user.
     * @param integer $mediaId
     */
    public function removeLike($mediaId) {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['remove_like'], $mediaId, $this->_accessToken);
        $this->_initHttpClient($endpointUrl, Zend_Http_Client::DELETE);
        return $this->_getHttpClientResponse();
    }

    /**
     * Get information about a tag object.
     * @param string $tagName
     */
    public function getTags($tagName) {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['tags'], $tagName, $this->_accessToken);
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Get a list of recently tagged media.
     * @param string $tagName
     * @param integer $maxId
     * @param integer $minId
     */
    public function getRecentTags($tagName, $maxId = '', $minId = '') {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['tags_recent'], $tagName, $maxId, $minId, $this->_accessToken);
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Search for tags by name - results are ordered first as an exact match, then by popularity.
     * @param string $tagName
     */
    public function searchTags($tagName) {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['tags_search'], urlencode($tagName), $this->_accessToken);
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Get information about a location.
     * @param integer $id
     */
    public function getLocation($id) {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['locations'], $id, $this->_accessToken);
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Get a list of recent media objects from a given location.
     * @param integer $locationId
     */
    public function getLocationRecentMedia($id, $maxId = '', $minId = '', $maxTimestamp = '', $minTimestamp = '') {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['locations_recent'], $id, $maxId, $minId, $maxTimestamp, $minTimestamp, $this->_accessToken);
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }

    /**
     * Search for a location by name and geographic coordinate.
     * @see http://instagr.am/developer/endpoints/locations/#get_locations_search
     * @param float $lat
     * @param float $lng
     * @param integer $foursquareId
     * @param integer $distance
     */
    public function searchLocation($lat, $lng, $foursquareId = '', $distance = '') {
        $this->_init();
        $endpointUrl = sprintf($this->_endpointUrls['locations_search'], $lat, $lng, $foursquareId, $distance, $this->_accessToken);
        $this->_initHttpClient($endpointUrl);
        return $this->_getHttpClientResponse();
    }
}

class InstagramException extends Exception {
}
