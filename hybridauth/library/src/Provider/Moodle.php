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
    protected $apiBaseUrl = WP_SITEURL . '/moodle';
    protected $authorizeUrl = WP_SITEURL . '/moodle/local/oauth/login.php';
    protected $accessTokenUrl = WP_SITEURL . '/moodle/local/oauth/token.php';

    // @nacho: la variable scope se configura en wsl.authentication.php wsl_process_login_build_provider_config

    /**
     * @throws HttpClientFailureException
     * @throws HttpRequestFailedException
     * @throws InvalidAccessTokenException
     * @throws Exception
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest("/local/oauth/user_info.php", 'GET', array(
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
