<?php

namespace App\Services\Moysklad;

use App\Models\Setting;

class SupplyService
{
    private $sypply;

    public function __construct()
    {
        $this->supply = $this->createSupply();
    }

    public function createSupply()
    {
        $storeService = new StoreService();
        $currenciesService = new CurrenciesService();
        $organizationService = new OrganizationService();
        $counterpartyService = new CounterpartyService();

        $newSupply = [
            'agent' => Service::formMeta($counterpartyService->getHelpwizorCounterparty()->meta),
            'organization' => Service::formMeta($organizationService->getOrganization()->meta),
            'store' => Service::formMeta($storeService->getStore()->meta),
            'rate' => (object)['currency' => Service::formMeta($currenciesService->getCurrency(Setting::get('currency'))->meta)],
            'applicable' => true
        ];

        $client = new Client();
        $res = $client->postGuzzle('supply', [
            'json' => $newSupply
        ]);

        return json_decode($res->getBody()->getContents());
    }

    public function storeSupplyPosition($assortmentMeta, $quantity, $sync = null)
    {
        if (!$quantity) {
            return null;
        }
        $newPosition = [
            'assortment' => Service::formMeta($assortmentMeta),
            'quantity' => $quantity
        ];
        $uri = 'supply/' . $this->getSupply()->id . '/positions';

        $client =new Client();
        try {
            $client->postGuzzle($uri, [
                'json' => $newPosition
            ]);
        } catch (\Exception $exception) {
            Service::exception($exception);
        }
    }

    private function getSupply()
    {
        return $this->supply;
    }
}
