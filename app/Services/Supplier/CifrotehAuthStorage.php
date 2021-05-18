<?php

namespace App\Services\Supplier;

//OAuth oauth_consumer_key="0a42631a95c8ac7c21e7fc3a1bf762c1", oauth_nonce="9jzYGKWJXdEWu2L84UqWmwIUID0uFmIO", oauth_signature_method="HMAC-SHA1", oauth_timestamp="1596206337", oauth_version="1.0", oauth_token="6dd7935aacedae6ce2994a9ae9c2fe1a", oauth_signature="xtZA79ojJ1J%2BVY45PQYxw19%2F%2BnY%3D"
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Token\TokenInterface;

class CifrotehAuthStorage implements TokenStorageInterface
{
    public function retrieveAccessToken($service_name)
    {
        return config("suppliers.$service_name");
    }

    public function storeAccessToken($service, TokenInterface $token)
    {

    }

    public function hasAccessToken($service)
    {

    }

    public function clearToken($service)
    {

    }

    public function clearAllTokens()
    {

    }
}