<?php

namespace App\Http\Controllers\Admin;

use App\Models\Testimonial;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TestimonialController extends Controller
{
    //
    public function index(Request $request)
    {
        $testimonials = Testimonial::with('product')
            ->orderBy($request->input('sort_column', 'id'), $request->input('sort_direction', 'desc'))
            ->paginate($request->input('perPage'));

        return response()->json(
            compact('testimonials')
        );
    }

    public function update(Request $request, $id)
    {

        $testimonial = Testimonial::findOrFail($id);

        $request->validate([
            'status' => ['required', 'boolean']
        ]);

        $testimonial->status = $request->input('status');

        $testimonial->save();

        return response()->json([
            'text' => trans('form.result.success-updated')
        ]);
    }

    public function destroy($id)
    {
        //
        Testimonial::findOrFail($id)->delete();

        return response()->json([
            'text' => trans('form.result.success-deleted')
        ]);
    }
}
