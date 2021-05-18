<?php

namespace App\Services\Moysklad;

class StoreService
{

    private $stores;

    public function __construct()
    {
        $client = new Client();
        $this->stores = json_decode($client->getGuzzle('store')->getBody()->getContents());

        if (empty($this->stores->rows)) {
            $this->create();
            $this->stores = json_decode($client->getGuzzle('store')->getBody()->getContents());
        }
    }

    public function create($name = 'Основной склад')
    {
        $client = new Client();
        $data = [
            'name' => $name
        ];

        $client->postGuzzle('store', [
            'json' => $data
        ]);
    }

    public function getStore()
    {
        return $this->stores->rows[0];
    }
}
