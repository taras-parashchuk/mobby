<?php

namespace App\Http\Controllers\Admin;


use App\Models\ExportProductsList;
use App\Models\ProductToExportProductsList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use DB;

class ExportProductsListController extends Controller
{
    //
    private $request;

    public function index(Request $request)
    {
        $query = ExportProductsList::with(['products' => function ($q) {
            $q->select('products.id', 'slug', 'type', 'image', 'sku', 'main_id');
        }, 'products.translates' => function ($q) {
            $q->select('product_id', 'locale', 'name');
        }, 'products.main_product']);

        if ($request->has('autocomplete')) {
            $query = $query->where('name', 'like', $request->input('name') . '%');
        }

        $rows = $query->get();

        $rows->each(function ($row){
           $row->products->each(function ($product){
               $product->append('translate', 'filemanager_thumb');
           });
        });

        return $rows;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', Rule::unique('export_products_lists')],
            'products.*.id' => 'exists:products,id'
        ]);

        $this->request = $request;

        $products_list = new ExportProductsList();

        $this->save($products_list);

        return response()->json([
            'id' => $products_list->id,
            'text' => trans('form.result.success-created')
        ]);

    }

    public function update($id, Request $request)
    {
        $request->merge([
            'id' => $id
        ]);

        $request->validate([
            'id' => 'required|exists:export_products_lists',
            'name' => ['required', Rule::unique('export_products_lists')->ignore($id)],
            'products.*' => 'exists:products,id'
        ]);

        $this->request = $request;

        $products_list = ExportProductsList::find($request->input('id'));

        $this->save($products_list);

        return response()->json([
            'id' => $products_list->id,
            'text' => trans('form.result.success-updated')
        ]);

    }

    public function destroy(Request $request, $id)
    {
        $request->merge([
            'id' => $id
        ]);

        $request->validate([
            'id' => 'exists:export_products_lists'
        ]);

        ExportProductsList::find($id)->delete();

        return response()->json([
            'text' => trans('form.result.success-deleted')
        ]);
    }

    public function addProductsToList(Request $request)
    {
        $request->validate([
            'export_products_lists' => ['required', 'array'],
            'export_products_lists.*' => ['exists:export_products_lists,id'],
            'products' => ['required', 'array'],
            'products.*' => ['exists:products,id'],

        ]);

        foreach ($request->input('export_products_lists') as $products_list_id){

            $inserts = [];

            foreach ($request->input('products', []) as $product_id) {

                $inserts[] = [
                    'products_list_id' => $products_list_id,
                    'product_id' => $product_id
                ];

            }

            DB::table('product_to_export_products_lists')->insertOrIgnore($inserts);

        }

        return response()->json([
            'text' => trans('form.result.success-updated')
        ]);
    }

    private function save(&$model)
    {
        $model->name = $this->request->input('name');

        $model->save();

        $model->to_products()->delete();

        $products = collect();

        foreach ($this->request->input('products', []) as $product_id) {

            $to_product = new ProductToExportProductsList();
            $to_product->products_list_id = $this->request->input('id');
            $to_product->product_id = $product_id;

            $products->push($to_product);
        }

        $model->to_products()->saveMany($products);
    }

}
