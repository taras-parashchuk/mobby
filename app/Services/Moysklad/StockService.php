<?php

namespace App\Services\Moysklad;

use App\Models\Syncs\ExternalApi;
use GuzzleHttp;

class StockService
{
    public static function getStocks($sync, $offset = 0, $limit = 25)
    {
        $externalApi = ExternalApi::where('code', config('syncs.moysklad.externalCode'))->first();

        $client = new GuzzleHttp\Client([
            'auth' => [$externalApi->login, $externalApi->password]
        ]);

        $stocks = [];


        $res = null;
        try {
            $res = $client->get('https://online.moysklad.ru/api/remap/1.1/report/stock/all', [
                'query' => [
                    'offset' => $offset,
                    'limit' => $limit
                ]
            ]);
        } catch (\Exception $exception) {
            Service::exception($exception, $sync);
            return $stocks;
        }

        $stocks_info = json_decode($res->getBody()->getContents());

        $stocks['rows'] = $stocks_info->rows;
        $stocks['total'] = $stocks_info->meta->size;

        return $stocks;
    }
}
