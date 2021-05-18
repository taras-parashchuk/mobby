<?php

namespace App\Helpers\Excel\Imports;

use App\Models\Language;
use App\Models\Setting;
use App\Models\Sync;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;

use App\Models\Category;
use App\Models\CategoryTranslation;
use Str;

class ExcelImportCategories implements OnEachRow, WithChunkReading, WithEvents
{

    use RegistersEventListeners;

    private $locale;
    private $name;
    private $parent_id;
    private $category_extra_1;

    private $sync;
    private $total_rows;

    public function __construct(Sync $syncJob, $configuration, $locale)
    {
        $this->locale = $locale;

        $this->configuration = $configuration;

        $this->sync = $syncJob;

    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {

                if($this->sync->type !== 'Export Groups Sheet'){
                    $this->sync->current = 0;
                    $this->sync->type = 'Export Groups Sheet';
                }

                $this->sync->total = $event->sheet->getDelegate()->getHighestDataRow() - 1;

                $this->sync->save();
            },
        ];
    }

    public function onRow(Row $row)
    {

        $rowIndex = $row->getIndex();
        $row = $row->toArray();

        if ($rowIndex === 1 || $this->sync->current > $rowIndex - 1) return;

        $this->fromCustom($row);

        if ($rowIndex > 1) {

            $this->sync->current++;
            $this->sync->success_categories_count++;

            $this->sync->update();

            $this->sync->refresh();

            if($this->sync->breaked){
                $this->break();
            }
        }
    }

    public function chunkSize(): int
    {
        return 10;
    }

    private function getCategory($extra_1)
    {
        return Category::with('translates')->where('extra_1', '=', $extra_1)->first();
    }

    private function fromCustom($row)
    {
        $this->category_extra_1 = $row[0];
        $this->name = $row[1];
        $this->parent_id = $row[3];

        if ($this->parent_id && !$this->getCategory($this->parent_id)) {
            return;
        } else {

            $category = $this->getCategory($this->category_extra_1);

            if ($category) {

                $translation = $category->translates->firstWhere('locale', $this->locale);

                try {
                    $translation->name = $this->name;
                } catch (\Exception $e) {
                    $translation = new CategoryTranslation();

                    $translation->category_id = $category->id;
                    $translation->locale = $this->locale;
                    $translation->name = $this->name;

                }

                $translation->save();

            } else {

                $category = new Category();

                $category->parent_id = $this->getCategory($this->parent_id)->id ?? 0;
                $category->slug = Str::slug($this->name, '-', $this->locale);
                $category->extra_1 = $this->category_extra_1;

                $translation = new CategoryTranslation();

                $translation->locale = $this->locale;
                $translation->name = $this->name;

                $category->status = false;
                $category->save();

                $category->translates()->save($translation);

                $category->status = true;
                $category->save();

            }
        }
    }

    private function break()
    {
        \DB::connection('system')->table('jobs')->where('id', $this->sync->job_id)->delete();

        die();
    }
}