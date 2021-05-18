<?php


namespace App\Services\Moysklad;


use App\Models\Currency;

class CurrenciesService
{
    const CURRENCIES_CODE = [
        'UAH' => '980',
        'USD' => '840',
        'RUB' => '643',
        'EUR' => '978'
    ];

    private $currencies;

    public function __construct()
    {
        $client = new Client();
        $currencies = json_decode($client->getGuzzle('currency/')->getBody()->getContents());

        foreach ($currencies->rows as $item) {
            $this->currencies[] = $item;
        }
    }

    public function getCurrency($isoCode = [])
    {
        if (empty($isoCode)) {
            return $this->currencies;
        }

        return \Arr::first($this->currencies, function($elem) use ($isoCode){
            return $elem->isoCode == $isoCode;
        });

    }

    public function getExternalIsoCode($href)
    {
        if($finded_currency = \Arr::first($this->currencies, function($elem) use ($href){
            return $elem->meta->href == $href;
        })){
            return $finded_currency->isoCode;
        }
    }

    public function updateDBCurrenciesRate($sync)
    {
        foreach ($this->currencies as $key => $currency) {
            $dbCurrency = Currency::where('code', $currency->isoCode)->first();

            if (!is_null($dbCurrency)) {
                $dbCurrency->exchange_rate = (string)$currency->rate;
                $dbCurrency->save();
            }

            $sync->current++;
            $sync->update();
        }
    }

    public function uploadCurrencies($currencies, $sync)
    {
        foreach ($currencies as $currency) {
            if (!array_key_exists($currency->code, $this->currencies)) {
                $data = [
                    'name' => $currency->name,
                    'rate' => (float)$currency->exchange_rate,
                    'code' => self::CURRENCIES_CODE[$currency->code],
                    'isoCode' => $currency->code
                ];

                $client = new Client();
                try {
                    $result = $client->postGuzzle('currency', [
                        'json' => $data
                    ]);
                } catch (\Exception $exception) {
                    Service::exception($exception, $sync);
                }
            }

            $sync->current++;
            $sync->update();
        }
    }

    public function downloadCurrencies($sync)
    {
        foreach ($this->currencies  as $currency) {
            Currency::firstOrCreate(['code' => $currency->isoCode], [
                'name' => $currency->name,
                'exchange_rate' => (string)$currency->rate,
                'active' => true,
                'symbol' => $currency->isoCode,
                'format' => '10.00',
            ]);

            $sync->current++;
            $sync->update();
        }
    }

    public function getCurrencies()
    {
        return $this->currencies;
    }
}
