<?php

namespace App\Http\Controllers;

use App\Helpers\HelperFunction;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialsController extends Controller
{
    //
    public function index()
    {
        $testimonials = Testimonial::where('status', 1)->get();

        return view('pages.testimonials', compact('testimonials'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'firstname' => ['bail', 'required', 'string', 'min:2', 'max:64'],
            'email' => ['bail', 'required', 'email'],
            'review' => ['bail', 'required', 'string', 'between:10,1000'],
            'rating' => ['bail', 'required', 'numeric', 'between:1,5'],
            'product_id' => ['nullable', 'exists:products,id']
        ]);

        $testimonial = new Testimonial;

        $testimonial->user_id = \Auth::id() ?? null;
        $testimonial->rating = $request->input('rating', 4);
        $testimonial->name = $request->input('firstname');
        $testimonial->email = $request->input('email', null);
        $testimonial->text = $request->input('review');
        $testimonial->product_id = $request->input('product_id', null);


        $testimonial->save();

        return response()->json([
            'success' => trans('form.result.success-sent'),
        ]);

    }
}
