<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Information;
use App\Models\Product;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    //
    public function index()
    {
        $output  = '<?xml version="1.0" encoding="UTF-8"?>';
        $output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

        $this->setCategories($output);

        $this->setInformations($output);

        $this->setProducts($output);

        $output .= '</urlset>';

        return response($output, 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    private function setCategories(&$output)
    {
        Category::enabled()->get(['id','slug'])->each(function($category) use (&$output){
            $output .= '<url>';
            $output .= '<loc>' . $category->href . '</loc>';
            $output .= '<changefreq>weekly</changefreq>';
            $output .= '<priority>0.7</priority>';
            $output .= '</url>';
        });
    }

    private function setInformations(&$output)
    {
        Information::enabled()->get(['id','slug'])->each(function($item) use (&$output){
            $output .= '<url>';
            $output .= '<loc>' . $item->href . '</loc>';
            $output .= '<changefreq>weekly</changefreq>';
            $output .= '<priority>0.5</priority>';
            $output .= '</url>';
        });
    }

    private function setProducts(&$output)
    {
        Product::notVariant()->enabledAndDelivered()->with('primary_variant')->get(['id','slug','type','primary_variant_id'])->each(function($item) use (&$output){
            $output .= '<url>';
            $output .= '<loc>' . $item->href . '</loc>';
            $output .= '<changefreq>weekly</changefreq>';
            $output .= '<priority>1.0</priority>';
            $output .= '</url>';
        });
    }
}
