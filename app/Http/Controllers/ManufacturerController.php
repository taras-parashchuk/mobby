<?php

namespace App\Http\Controllers;

use App\Models\Manufacturer;
use Illuminate\Http\Request;

class ManufacturerController extends Controller
{
    //
    public function index($slug, $id)
    {
        $manufacturer = Manufacturer::with('translate')->where('slug', $slug)->findOrFail($id);

        dd($manufacturer);
    }
}
