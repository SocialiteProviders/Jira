<?php

namespace SocialiteProviders\Jira;

use SocialiteProviders\Manager\OAuth1\AbstractProvider;
use SocialiteProviders\Manager\OAuth1\User;

class Provider extends AbstractProvider
{
    /**
     * Unique Provider Identifier.
     */
    const IDENTIFIER = 'JIRA';

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user->extra)->map([
            'id' => $user['key'], 'nickname' => $user['name'],
            'name' => $user['name'], 'email' => $user['email'],
            'avatar' => array_get($user, 'avatar'),
        ]);
    }
}
