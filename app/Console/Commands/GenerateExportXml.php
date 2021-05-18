<?php

namespace App\Console\Commands;

use App\Models\ExportConfiguration;
use App\Models\Language;
use App\Services\XmlExport;
use Illuminate\Console\Command;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Console\Exception\RuntimeException;


class GenerateExportXml extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xml-export:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new xml file for export';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        ExportConfiguration::all()->each(function($configuration){
            (new XmlExport($configuration))->generate()->saveXml();
        });
    }

}
