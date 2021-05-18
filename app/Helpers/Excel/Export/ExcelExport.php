<?php

namespace App\Helpers\Excel\Export;

use App\Models\ProductSpecial;
use App\Models\Setting;
use App\Models\SyncConfiguration;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExcelExport implements WithMultipleSheets
{
    private $configuration;

    public function __construct(SyncConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function sheets(): array
    {
        $sheets =  [
            new Categories($this->configuration),
            new Products($this->configuration),
        ];

        return $sheets;
    }
}