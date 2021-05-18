<?php

namespace App\Services;

use DB;
use App\Models\Shippings\NovaPoshtaArea;
use LisDev\Delivery\NovaPoshtaApi2;
use App\Models\Shipping;

class NovaPoshta
{
    private static $np;

    public static function generate()
    {
        $settings = Shipping::where('code', 'nova_poshta')->first()->settings;

        $settings = json_decode($settings, true);

        self::$np = new NovaPoshtaApi2(
            $settings['api_key'],
            'ua'
        );

        NovaPoshtaArea::all()->each->delete('ref');

        DB::table('nova_poshta_departments')->truncate();

        if ($result = self::$np->getAreas()) {
            foreach ($result['data'] as $area) {
                $nova_poshta_area = new NovaPoshtaArea();

                $nova_poshta_area->ref = $area['Ref'];
                $nova_poshta_area->value = $area['Description'];

                $nova_poshta_area->save();

            }
        }

        $page_number = 1;

        while ($result = self::$np->getCities($page_number)) {

            foreach (array_chunk($result['data'], 200) as $cities_group) {

                $new_cities = [];

                foreach ($cities_group as $city){
                    $nova_poshta_city = [];

                    $nova_poshta_city['ref'] = $city['Ref'];
                    $nova_poshta_city['value'] = $city['Description'];
                    $nova_poshta_city['area_ref'] = $city['Area'];

                    $new_cities[] = $nova_poshta_city;
                }

                DB::table('nova_poshta_cities')->insert($new_cities);

            }

            $page_number++;

            if(empty($result['data'])) break;
        }

        $page_number = 1;

        while($result = self::$np->model('Address')->method('getWarehouses')->params([
            'Page' => $page_number,
            'Limit' => 400,
        ])->execute()){
            foreach (array_chunk($result['data'], 400) as $departments_group) {

                $new_departments = [];

                foreach ($departments_group as $department){

                    $new_departments[] = [
                        'ref' => $department['Ref'],
                        'value' => $department['Description'],
                        'city_ref' => $department['CityRef']
                    ];
                }

                DB::table('nova_poshta_departments')->insert($new_departments);
            }

            $page_number++;

            if(empty($result['data'])) break;
        }
    }
}