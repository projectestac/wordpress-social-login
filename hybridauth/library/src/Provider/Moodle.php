<?php

namespace Hybridauth\Provider;

use Exception;
use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\HttpClientFailureException;
use Hybridauth\Exception\HttpRequestFailedException;
use Hybridauth\Exception\InvalidAccessTokenException;
use Hybridauth\User;


class Moodle extends OAuth2
{
    protected $apiBaseUrl;
    protected $authorizeUrl;
    protected $accessTokenUrl;

    // @nacho: la variable scope se configura en wsl.authentication.php wsl_process_login_build_provider_config

    public function __construct($config = [], HttpClientInterface $httpClient = null, StorageInterface $storage = null, LoggerInterface $logger = null)
    {
        // Fully load WordPress to get function get_option() working
        require_once(dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))))) . '/wp-load.php');

        // Load Moodle URL from database
        $moodle_url = rtrim(get_option('wsl_settings_Moodle_url'), '/');

        $this->apiBaseUrl = $moodle_url;
        $this->authorizeUrl = $moodle_url . '/local/oauth/login.php';
        $this->accessTokenUrl = $moodle_url . '/local/oauth/token.php';

        parent::__construct($config, $httpClient, $storage, $logger);
    }

    /**
     * @throws HttpClientFailureException
     * @throws HttpRequestFailedException
     * @throws InvalidAccessTokenException
     * @throws Exception
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest('/local/oauth/user_info.php', 'GET', array(
                'grant_type' => 'authorization_code',
                'scope'      => 'user_info'
            )
        );

        if (!isset($response->id) || !isset($response->id) || isset($response->error)) {
            throw new Exception("User profile request failed! {$this->providerId} returned an invalid response.", 6);
        }

        $userProfile = new User\Profile();

        $userProfile->identifier = (property_exists($response, 'id')) ? $response->id : "";
        $userProfile->firstName = (property_exists($response, 'firstname')) ? $response->firstname : "";
        $userProfile->lastName = (property_exists($response, 'lastname')) ? $response->lastname : "";
        $userProfile->displayName = $response->username;
        $userProfile->email = (property_exists($response, 'email')) ? $response->email : "";
        $userProfile->emailVerified = $userProfile->email;
        $userProfile->country = (property_exists($response, 'country')) ? $response->country : "";
        $userProfile->phone = (property_exists($response, 'phone1')) ? $response->phone1 : "";
        $userProfile->address = (property_exists($response, 'address')) ? $response->address : "";
        $userProfile->language = (property_exists($response, 'lang')) ? $response->lang : "";
        $userProfile->description = (property_exists($response, 'description')) ? $response->description : "";

        return $userProfile;
    }

}
