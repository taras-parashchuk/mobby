<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CustomValidation;
use App\Models\BannerSlide;
use App\Models\Banner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BannerController extends Controller
{
    //

    private $request;

    use CustomValidation;

    public function index(Request $request)
    {
        if($request->input('perPage')){
            $banners = Banner::orderBy($request->input('sort_column', 'id'), $request->input('sort_direction', 'desc'))
                ->paginate($request->input('perPage'));
        }else{
            $banners = Banner::orderBy($request->input('sort_column', 'id'), $request->input('sort_direction', 'desc'))->get();
        }

        return compact('banners');
    }

    public function store(Request $request)
    {

        $this->request = $request;

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'status' => ['required', 'boolean']
        ]);

        $banner = new Banner;

        $banner->name = $request->input('name');
        $banner->status = $request->input('status');

        $banner->save();

        return response()->json([
            'id' => $banner->id,
            'text' => trans('form.result.success-created')
        ]);

    }

    public function destroy($id)
    {
        Banner::findOrFail($id)->delete();

        return response()->json([
            'text' => trans('form.result.success-deleted')
        ]);
    }

    public function edit($id)
    {
        $banner_info = Banner::with('slides')->findOrFail($id);

        $banner_info->slides->each->append('filemanager_thumb');

        return $banner_info;
    }

    public function update(Request $request, $id)
    {
        $this->request = $request;

        $banner = Banner::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'status' => ['required', 'boolean'],
            'slides.*.sort_order' => ['nullable', 'integer', 'digits_between:1,6'],
            'slides.*.title' => ['nullable', 'string', 'max:200'],
            'slides.*.locale' => ['required', 'exists:languages'],
            'slides.*.link' => ['nullable', 'url'],
            //'slides.*.image' => ['required', 'string', 'max:200'],
        ]);

        $banner->name = $request->input('name');
        $banner->status = $request->input('status');

        $banner->save();

        if(!$request->input('fast', false)){
            $banner->slides()->delete();

            $slides = [];

            foreach ($request->input('slides') as $slide){
                $bannerSlide = new BannerSlide();

                $bannerSlide->title = $slide['title'];
                $bannerSlide->link = $slide['link'];
                $bannerSlide->locale = $slide['locale'];
                $bannerSlide->sort_order = $slide['sort_order'];
                $bannerSlide->image = $slide['image'] ?? 'empty';

                $slides[] = $bannerSlide;
            }

            $banner->slides()->saveMany($slides);
        }

        return response()->json([
            'text' => trans('form.result.success-updated')
        ]);

    }
}
