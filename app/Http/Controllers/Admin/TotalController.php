<?php

namespace App\Http\Controllers\Admin;

use App\Models\Total;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TotalController extends Controller
{
    //
    public function index()
    {
        return Total::paginate();
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id' => ['exists:totals']
        ]);

        $total = Total::find($id);

        $total->setting = json_encode($request->input('decoded_setting'), JSON_UNESCAPED_UNICODE);

        $total->update();

        return response()->json([
            'id' => $total->id,
            'text' => trans('form.result.success-updated')
        ]);
    }
}
