# Jira OAuth2 Provider for Laravel Socialite

[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/SocialiteProviders/Jira.svg?style=flat-square)](https://scrutinizer-ci.com/g/SocialiteProviders/Jira/?branch=master)
[![Latest Stable Version](https://img.shields.io/packagist/v/socialiteproviders/jira.svg?style=flat-square)](https://packagist.org/packages/socialiteproviders/jira)
[![Total Downloads](https://img.shields.io/packagist/dt/socialiteproviders/jira.svg?style=flat-square)](https://packagist.org/packages/socialiteproviders/jira)
[![Latest Unstable Version](https://img.shields.io/packagist/vpre/socialiteproviders/jira.svg?style=flat-square)](https://packagist.org/packages/socialiteproviders/jira)
[![License](https://img.shields.io/packagist/l/socialiteproviders/jira.svg?style=flat-square)](https://packagist.org/packages/socialiteproviders/jira)

## Documentation

Full documentation for using this provider can be found at [Jira Documentation](http://socialiteproviders.github.io/providers/jira/)

## Generate key pair


You also need to generate key pair:
```
    mkdir storage/app/keys
    openssl genrsa -out storage/app/keys/jira.pem 1024
    openssl rsa -in storage/app/keys/jira.pem -pubout -out storage/app/keys/jira.pub
```
Your key is now in storage/app/keys/jira.pub


And add this to your .env file
```
JIRA_KEY="yourkey"
JIRA_SECRET="yoursecret"
JIRA_REDIRECT_URI="http://yoursite.com/social/auth/jira"
JIRA_BASE_URI="https://example.atlassian.net"
JIRA_CERT_PATH = "storage/app/keys/jira.pub"
```