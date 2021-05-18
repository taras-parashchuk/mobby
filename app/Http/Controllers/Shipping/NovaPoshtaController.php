<?php

namespace App\Http\Controllers\Shipping;

use App\Models\Shippings\NovaPoshtaArea;
use App\Models\Shippings\NovaPoshtaCity;
use App\Models\Shippings\NovaPoshtaDepartment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NovaPoshtaController extends Controller
{
    //
    public function translation()
    {
        return trans('components.shipping.nova_poshta');
    }

    public function areas()
    {
        return NovaPoshtaArea::all()->map(function($area){
            $area->key = 'area';

            return $area;
        });
    }

    public function cities(Request $request)
    {
        return NovaPoshtaCity::where('area_ref', $request->input('ref'))->get()->map(function($city){
            $city->key = 'city';

            return $city;
        });
    }

    public function departments(Request $request)
    {
        return NovaPoshtaDepartment::where('city_ref', $request->input('ref'))->get()->map(function($department){
            $department->key = 'department';

            return $department;
        });
    }
}
