<?php

namespace SocialiteProviders\Jira;

use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\Server as BaseServer;
use League\OAuth1\Client\Signature\SignatureInterface;
use League\OAuth1\Client\Credentials\ClientCredentialsInterface;

class Server extends BaseServer
{
    const JIRA_BASE_URL = 'http://example.jira.com';

    private $jiraBaseUrl;
    private $jiraCertPath;

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
            $this->jiraBaseUrl = isset($clientCredentials['base_url']) ? $clientCredentials['base_url'] : null;
            $this->jiraCertPath = isset($clientCredentials['cert']) ? $clientCredentials['cert'] : storage_path().'/app/keys/jira.pem';
            $clientCredentials = $this->createClientCredentials($clientCredentials);
        } elseif (!$clientCredentials instanceof ClientCredentialsInterface) {
            throw new \InvalidArgumentException('Client credentials must be an array or valid object.');
        }

        $this->clientCredentials = $clientCredentials;

        // !! RsaSha1Signature for Jira
        $this->signature = $signature ?: new RsaSha1Signature($clientCredentials);
    }

    /**
     * Get JIRA base URL.
     *
     * @return string
     */
    public function getJiraBaseUrl()
    {
        return empty($this->jiraBaseUrl) ? self::JIRA_BASE_URL : $this->jiraBaseUrl;
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
        $parameters['oauth_signature'] = $this->signature->sign($uri, $parameters, 'POST', $this->$jiraCertPath);

        return $this->normalizeProtocolParameters($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function urlTemporaryCredentials()
    {
        return self::JIRA_BASE_URL.'/plugins/servlet/oauth/request-token?oauth_callback='.
            rawurlencode($this->clientCredentials->getCallbackUri());
    }

    /**
     * {@inheritdoc}
     */
    public function urlAuthorization()
    {
        return self::JIRA_BASE_URL.'/plugins/servlet/oauth/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function urlTokenCredentials()
    {
        return self::JIRA_BASE_URL.'/plugins/servlet/oauth/access-token';
    }

    /**
     * {@inheritdoc}
     */
    public function urlUserDetails()
    {
        return self::JIRA_BASE_URL.'/rest/api/2/myself';
    }

    /**
     * {@inheritdoc}
     */
    public function userDetails($data, TokenCredentials $tokenCredentials)
    {
        return $data;
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
