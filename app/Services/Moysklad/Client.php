<?php

namespace App\Services\Moysklad;

use App\Models\Syncs\ExternalApi;
use GuzzleHttp;

class Client
{
    private $client;

    public function __construct($version = 1.1)
    {
        $externalApi = ExternalApi::where('code', config('syncs.moysklad.externalCode'))->first();

        $this->client = new GuzzleHttp\Client([
            'base_uri' => 'https://online.moysklad.ru/api/remap/' . $version . '/entity/',
            'auth' => [$externalApi->login, $externalApi->password]
        ]);
    }

    public function getGuzzle( $uri, array $params = [])
    {
        return $this->client->get($uri, $params);
    }

    public function postGuzzle($uri, array $params = [])
    {
        return $this->client->post($uri, $params);
    }

    public function putGuzzle($uri, array $params = [])
    {
        return $this->client->put($uri, $params);
    }
}
