<?php

namespace App\Models;

use App\Events\NewTestimonialEvent;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class Testimonial extends Model
{
    //
    use NodeTrait;

    protected $dispatchesEvents = [
        'created' => NewTestimonialEvent::class,
    ];

    protected $appends = ['date_added', 'href'];

    protected $casts = [
        'status' => 'boolean'
    ];

    public function getDateAddedAttribute()
    {
        if( $this->created_at) return $this->created_at->format('d.m.y');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function getHrefAttribute()
    {
        if($this->product_id){
            $href = $this->product ? $this->product->href : '';
        }else{
            $href = route('testimonials');
        }

        return $href;
    }
}
