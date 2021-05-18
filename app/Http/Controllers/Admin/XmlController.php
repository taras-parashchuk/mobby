<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\FileManager;
use App\Http\Requests\Admin\XmlAddToQueueRequest;
use App\Http\Requests\GetXmlSourceCategories;
use App\Jobs\XmlImportJob;
use App\Models\Sync;
use App\Models\SyncConfiguration;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class XmlController extends Controller
{
    use DispatchesJobs;

    private $configuration_settings;

    public function addToQueue(XmlAddToQueueRequest $request)
    {
        $request->validateResolved();

        $configuration = SyncConfiguration::find($request->input('configuration_id'));

        $sync = new Sync();
        $sync->data_type = 'xml';
        $sync->manually = true;
        $sync->configuration_id = $configuration->id;

        $sync->save();

        \Storage::disk('uploads')->putFileAs("uploads/files/sync/xml", $request->file('file'), "manually.xml");

        $job = (new XmlImportJob($sync->id, "uploads/files/sync/xml/manually.xml") );//->onConnection('sync');

        $job_id = $this->dispatch($job);

        try{
            $sync->job_id = $job_id;
        }catch (QueryException $exception){

        }

        $sync->save();

        $sync->refresh();

        return response()->json([
            'text' => trans('form.result.success-file-added-to-queue'),
            'queue' => $sync->toArray(),
        ]);

    }

    public function getQueueStatus()
    {
        return $syncJob = Sync::where('data_type', 'xml')
            ->where('manually', true)
            ->orderBy('id', 'desc')
            ->first();
    }

    public function breakManual($id)
    {
        $syncJob = Sync::findOrFail($id);

        $syncJob->breaked = true;
        $syncJob->update();

        return response()->json([
            'text' => trans('form.result.success-breaked-import'),
            'queue' => null,
        ]);

    }

    public function downloadReport($id)
    {
        return \Storage::disk('uploads')->download(Sync::find($id)->log_file_path);
    }

    public function getSourceCategories(GetXmlSourceCategories $request)
    {
        $request->validateResolved();

        $result = [
            'categories' => []
        ];

        $xml_content = $request->file_content;

        $configuration = $request->configuration_settings;

        $this->configuration_settings = $configuration;

        $categories_content = $xml_content;

        foreach (array_merge($configuration->paths->categories_container, $configuration->paths->category_tag) as $item){
            $categories_content = $categories_content->{$item};
        }

        for($i = 0; $i < count($categories_content); $i++){

            $category = $categories_content[$i];

            $result['categories'][] = [
                'id' => (string)$this->getElementByPath($category, 'category_id'),
                'parent_id' => (string)$this->getElementByPath($category, 'category_parent_id') ?: 0,
                'name' => implode(' - ', array_reverse($this->fillChildrenCategoryName($categories_content, $category))),
            ];
        }

        return $result;
    }

    private function fillChildrenCategoryName($list, $category, $names = [])
    {
        $current_name = (string)$this->getElementByPath($category, 'category_name') ?: '';

        $names[] = $current_name;

        $parent_id = $this->getElementByPath($category, 'category_parent_id');

        if($parent_id) $parent_id = (string)$parent_id;

        if($parent_id){

            foreach ($list as $category){

                $s_category_id = $this->getElementByPath($category, 'category_id') ?: null;

                if($s_category_id == $parent_id){

                    $names = $this->fillChildrenCategoryName($list, $category, $names);

                    break;
                }
            }
        }

        return $names;

    }

    private function getElementByPath($content, $path_code)
    {
        $values = $this->configuration_settings->paths->{$path_code};

        if($values){
            foreach ($values as $value){
                if (isset($content->{$value})) {
                    $content = $content->{$value};
                }elseif (isset($content[$value])){
                    $content = $content[$value];
                } elseif($content->attributes() || $content->children()->count()){
                    $content = null;
                }
            }
        }

        return $content;

    }

}
