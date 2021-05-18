<?php

namespace App\Http\Controllers\Admin;

use App\Events\NewCategoryEvent;
use App\Events\UpdateCategoryEvent;
use App\Helpers\CustomValidation;
use App\Models\Category;
use App\Models\CategoryFilterAttribute;
use App\Models\Language;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CategoryTranslation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    protected $rules;

    use CustomValidation;

    public function __construct()
    {
        $this->rules = [
            'image' => ['nullable', 'string', 'max:200'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'sort_order' => ['integer', 'digits_between:1,6'],
            'status' => ['boolean'],
            'slug' => ['nullable', 'string', 'max:120'],
            'extra_1' => ['nullable', 'string', 'max:240'],
            'extra_2' => ['nullable', 'string', 'max:240'],
            'translates.*.locale' => ['exists:languages,locale'],
            'translates.*.name' => ['required', 'string', 'max:200'],
            'translates.*.meta_title' => ['nullable', 'string', 'max:200'],
            'translates.*.meta_description' => ['nullable', 'string', 'max:200'],
            'translates.*.meta_keywords' => ['nullable', 'string', 'max:250'],
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        if ($request->input('exclude_id')) {

            $categories = Category::join('category_translations as ct', 'categories.id', '=', 'ct.category_id')
                ->without('translate')
                ->select('categories.id', 'ct.name', 'sort_order')
                ->orderBy('categories.sort_order')
                ->orderBy('ct.name')
                ->where([
                    'ct.locale' => app()->getLocale()
                ]);

            if ($request->input('phrase')) {
                $categories = $categories->where('ct.name', 'like', "%{$request->input('phrase')}%");
            }

            $categories = $categories->where('categories.id', '<>', $request->input('exclude_id'))->get();
        } elseif ($request->input('autocomplete')) {

            $categories = Category::without('translates')
                ->withTranslate()
                ->select('categories.id', 'categories.parent_id', 'categories._rgt', 'categories._lft', 'ct.name')
                ->where('ct.locale', Setting::get('admin_language'))
                ->withDepth()
                ->get()->toFlatTree();

            $categories = $categories->map(function ($category) {

                $i = 0;

                $prefix = '';

                while ($i < $category->depth) {
                    $prefix .= '→ ';

                    $i++;
                }

                $category->name = $prefix . $category->name;

                return $category;

            });

        } else {

            $categories = Category::get();

            $categories->each(function ($category) {
                $this->repairTranslates($category);
            });

            $categories = $categories->toTree();
        }

        return response()->json(
            compact('categories')
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $category = (new Category)->with('translates');

        $category->parent_id = 0;
        $category->sort_order = '';
        $category->status = 0;
        $category->slug = '';

        $locales = Language::where('status', 1)->orderBy('sort_order', 'asc')->pluck('locale');

        $translates = collect();

        foreach ($locales as $locale) {
            $translates->push([
                'name' => '',
                'locale' => $locale,
                'description',
                'meta_title',
                'meta_description',
                'meta_keywords'
            ]);
        }

        $category->translates = collect($translates);

        return response()->json(compact('category'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        unset($this->rules['parent_id'], $this->rules['status']);

        $request->validate($this->rules, $this->messages($request));

        $category = new Category;

        $category->sort_order = $request->sort_order;

        $default_trans_index = array_search(app()->getLocale(), array_column($request->input('translates'), 'locale'));

        $category->slug = Str::slug($request->input('translates.' . $default_trans_index . '.name'), '-', app()->getLocale());

        $translations = [];

        foreach ($request->input('translates') as $translate) {
            $translations[] = new CategoryTranslation(
                [
                    'name' => $translate['name'],
                    'locale' => $translate['locale']
                ]
            );
        }

        try{
            \DB::transaction(function () use ($category, $translations){

                $category->save();

                $category->translates()->saveMany($translations);

            });

            event(new NewCategoryEvent($category));

        }catch (\Exception $exception){
            throw ValidationException::withMessages([$exception->getMessage()]);
        }

        return response()->json([
            'id' => $category->id,
            'slug' => $category->slug,
            'href' => $category->href,
            'text' => trans('form.result.success-created')
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        //
        if (!$request->ajax()) {
            return view('admin.index');
        } else {
            $category = Category::without('translate')
                ->with(['translates', 'attributes' => function ($q) {
                    $q->groupBy('id');
                }, 'attributes.translates', 'filtered_attributes'])
                ->findOrFail($id);

            $this->repairTranslates($category);

            $category->append('filemanager_thumb');

            $filtered_attributes = [];

            $category->attributes->each(function ($attribute) use ($category, &$filtered_attributes) {
                $filtered_attributes[] = [
                    'id' => $attribute->id,
                    'title' => $attribute->translate->name,
                    'status' => (bool)$category->filtered_attributes->firstWhere('attribute_id', $attribute->id)
                ];
            });

            unset($category->attributes);

            $category->attributes = $filtered_attributes;

            return response()->json(compact('category'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if ($request->input('fast')) {

            $category = Category::with('translates')->findOrFail($id);

            unset($this->rules['parent_id']);

            $request->validate($this->rules, $this->messages());

            $category->sort_order = $request->input('sort_order');
            $category->status = $request->input('status');

            try{

                \DB::transaction(function () use ($category, $request){
                    $category->translates()->delete();

                    foreach ($request->input('translates') as $translate) {
                        $category->translates()->save(new CategoryTranslation([
                            'locale' => $translate['locale'],
                            'name' => $translate['name'],
                            'description' => $translate['description'] ?? '',
                            'meta_keywords' => $translate['meta_keywords'] ?? '',
                            'meta_title' => $translate['meta_title'] ?? '',
                            'meta_description' => $translate['meta_description'] ?? '',
                        ]));
                    }

                    $category->save();
                });

                event(new UpdateCategoryEvent($category));

            }catch (\Exception $exception){
                throw ValidationException::withMessages([$exception->getMessage()]);
            }

        } else {

            $data = json_decode($request->getContent(), true);

            $category = Category::findOrFail($id);

            if (!isset($data['parent_id']) || !$data['parent_id']) unset($this->rules['parent_id']);

            Validator::make($data, $this->rules, $this->messages($data))->validate();

            $this->save($category, $data);

            CategoryFilterAttribute::where('category_id', $id)->get()->each->delete();

            foreach (array_filter($data['attributes'], function($attribute){
              return (bool)$attribute['status'];
            }) as $attribute){
                CategoryFilterAttribute::create([
                    'category_id' => $id,
                    'attribute_id' => $attribute['id']
                ]);
            }

        }

        return response()->json([
            'id' => $category->id,
            'slug' => $category->slug,
            'text' => trans('form.result.success-updated')
        ]);

    }

    public function updateHierarchy(Request $request)
    {
        $this->updateCategoriesByHierarchy($request->input('categories', []), 0);

        return response()->json([
            'text' => trans('form.result.success-updated')
        ]);
    }

    private function updateCategoriesByHierarchy($categories, $parent_id)
    {
        foreach ($categories as $sort_order => $input_category) {
            $category = Category::find($input_category['id']);

            if ($category) {
                $category->parent_id = $parent_id;
                $category->sort_order = $sort_order;

                $category->save();

                event(new UpdateCategoryEvent($category));

                if (count($input_category['children'])) {
                    $this->updateCategoriesByHierarchy($input_category['children'], $input_category['id']);
                }
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        Category::with('translates')->findOrFail($id)->delete();

        return response()->json([
            'text' => trans('form.result.success-deleted')
        ]);
    }

    private function save(Category &$category, $data)
    {

        $default_trans_index = array_search(app()->getLocale(), array_column($data['translates'], 'locale'));

        $category->image = $data['image'] ?? '';
        $category->parent_id = $data['parent_id'] ?? 0;
        $category->sort_order = (int)$data['sort_order'];
        $category->status = (int)$data['status'];
        $category->extra_1 = $data['extra_1'] ?: null;
        $category->extra_2 = $data['extra_2'] ?: null;

        if (isset($data['slug']) && $data['slug']) {
            $category->slug = $data['slug'];
        } else {
            $category->slug = Str::slug($data['translates'][$default_trans_index]['name'], '-', app()->getLocale());
        }

        $CategoryTranslations = [];

        foreach ($data['translates'] as $translate) {
            $CategoryTranslations[] = new CategoryTranslation((array)$translate);
        }

        try{

            \DB::transaction(function () use ($category, $CategoryTranslations){
                $category->save();

                $category->translates()->delete();

                $category->translates()->saveMany($CategoryTranslations);
            });

            event(new UpdateCategoryEvent($category));

        }catch (\Exception $exception){
            throw ValidationException::withMessages([$exception->getMessage()]);
        }


    }

    private function repairTranslates(&$model)
    {
        foreach (config('settings.languages') as $language) {

            if (!$model->translates->firstWhere('locale', 'like', $language['locale'])) {
                $model->translates->push(
                    new CategoryTranslation([
                        'name' => '',
                        'marketplace_name' => '',
                        'description' => '',
                        'meta_h1' => '',
                        'meta_title' => '',
                        'meta_description' => '',
                        'meta_keywords' => '',
                        'locale' => $language['locale']
                    ])
                );
            }
        }
    }
}
