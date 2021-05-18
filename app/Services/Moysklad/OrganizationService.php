<?php

namespace App\Services\Moysklad;

class OrganizationService
{

    private $organizations;

    public function __construct()
    {
        $client = new Client();
        $this->organizations = json_decode($client->getGuzzle('organization')->getBody()->getContents());

        if (empty($this->organizations->rows)) {
            $this->create();
            $this->organizations = json_decode($client->getGuzzle('organization')->getBody()->getContents());
        }
    }

    public function create($name = 'helpwizor')
    {
        $client = new Client();
        $data = [
            'name' => $name
        ];

        $client->postGuzzle('organization', [
            'json' => $data
        ]);
    }

    public function getOrganization()
    {
        return $this->organizations->rows[0];
    }
}
