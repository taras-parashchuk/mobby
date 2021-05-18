<?php

namespace App\Http\Controllers;

use App\Models\ExportConfiguration;
use App\Services\XmlExport;

class ExportProducts extends Controller
{
    //
    public function index(int $configuration_id)
    {
        $exportConfiguration = ExportConfiguration::findOrFail($configuration_id);

        return response((new XmlExport($exportConfiguration))->getXmlString(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function force_list(int $configuration_id)
    {
        $exportConfiguration = ExportConfiguration::findOrFail($configuration_id);

        return response((new XmlExport($exportConfiguration, true))->getXmlString(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }
}
