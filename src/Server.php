<?php

namespace SocialiteProviders\Jira;

use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Signature\SignatureInterface;
use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use SocialiteProviders\Manager\OAuth1\Server as BaseServer;
use SocialiteProviders\Manager\OAuth1\User;

class Server extends BaseServer
{
    const JIRA_BASE_URL = 'http://example.jira.com';

    private $jiraBaseUrl;
    private $jiraCertPath;
    private $jiraUserDetailsUrl;

    /**
     * Create a new server instance.
     *
     * !! RsaSha1Signature
     *
     * @param ClientCredentialsInterface|array $clientCredentials
     * @param SignatureInterface               $signature
     */
    public function __construct($clientCredentials, SignatureInterface $signature = null)
    {
        // Pass through an array or client credentials, we don't care
        if (is_array($clientCredentials)) {
            $this->jiraBaseUrl = array_get($clientCredentials, 'base_url');

            $this->jiraUserDetailsUrl = array_get($clientCredentials, 'user_details_url');

            $this->jiraCertPath = array_get($clientCredentials, 'cert', $this->getConfig('cert_path', storage_path().'/app/keys/jira.pem'));

            $clientCredentials = $this->createClientCredentials($clientCredentials);
        } elseif (!$clientCredentials instanceof ClientCredentialsInterface) {
            throw new \InvalidArgumentException('Client credentials must be an array or valid object.');
        }

        $this->clientCredentials = $clientCredentials;

        // !! RsaSha1Signature for Jira
        $this->signature = $signature ?: new RsaSha1Signature($clientCredentials);
        $this->signature->setCertPath($this->jiraCertPath);
    }

    /**
     * Get JIRA base URL.
     *
     * @return string
     */
    public function getJiraBaseUrl()
    {
        return $this->getConfig('base_uri', self::JIRA_BASE_URL);
    }

    /**
     * Generate the OAuth protocol header for a temporary credentials
     * request, based on the URI.
     *
     * @param string $uri
     *
     * @return string
     */
    protected function temporaryCredentialsProtocolHeader($uri)
    {
        $parameters = $this->baseProtocolParameters();

        // without 'oauth_callback'
        $parameters['oauth_signature'] = $this->signature->sign($uri, $parameters, 'POST');

        return $this->normalizeProtocolParameters($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function urlTemporaryCredentials()
    {
        return $this->getJiraBaseUrl().'/plugins/servlet/oauth/request-token?oauth_callback='.
            rawurlencode($this->clientCredentials->getCallbackUri());
    }

    /**
     * {@inheritdoc}
     */
    public function urlAuthorization()
    {
        return $this->getJiraBaseUrl().'/plugins/servlet/oauth/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function urlTokenCredentials()
    {
        return $this->getJiraBaseUrl().'/plugins/servlet/oauth/access-token';
    }

    /**
     * {@inheritdoc}
     */
    public function urlUserDetails()
    {
        return empty($this->jiraUserDetailsUrl) ? $this->getJiraBaseUrl().'/rest/api/2/myself' : $this->jiraUserDetailsUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function userDetails($data, TokenCredentials $tokenCredentials)
    {
        $user = new User();

        $user->email = isset($data['email']) ? $data['email'] : null;
        $user->name = isset($data['name']) ? $data['name'] : null;

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        return $data['key'];
    }

    /**
     * {@inheritdoc}
     */
    public function userScreenName($data, TokenCredentials $tokenCredentials)
    {
        return $data['name'];
    }

    /**
     * {@inheritdoc}
     */
    public function userEmail($data, TokenCredentials $tokenCredentials)
    {
        return $data['email'];
    }
}
