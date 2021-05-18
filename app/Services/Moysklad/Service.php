<?php

namespace App\Services\Moysklad;

use App\Models\Sync;
use App\Models\Syncs\ExternalApi;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class Service
{
    public static function extractIdFromMeta($uuidHref)
    {
        $parameters = explode('?', $uuidHref);

        parse_str($parameters[1], $values);

        return $values['id'];
    }

    public static function extractId($href)
    {
        $parse = parse_url($href);
        $parameters = explode('/', $parse['path']);
        $id = end($parameters);

        return $id;
    }

    public static function getMeta($id, $type)
    {
        $data['href'] = 'https://online.moysklad.ru/api/remap/1.1/entity/' . $type . '/'. $id;
        $data['metadataHref'] = 'https://online.moysklad.ru/api/remap/1.1/entity/'. $type . '/metadata';
        $data['type'] = $type;
        $data['mediaType'] = 'application/json';

        return (object)$data;
    }

    public static function formMeta($metaData)
    {
        return (object)['meta' => $metaData];
    }

    public static function exception($exception, $sync = null, $message = null)
    {
        if ($exception->getCode() == 403) {
            $externalApi = ExternalApi::where('code', config('syncs.moysklad.externalCode'))->first();
            $externalApi->password = null;
            $externalApi->save();
            if (!is_null($sync)) {
                self::writeLog($sync->type, trans('auth.failed'));
            }
        }

        if (!is_null($sync)) {
            self::setFatalError($sync);
            self::break($sync->job_id);
            if (is_null($message)) {
                self::writeLog($sync->type, trans('moy-sklad.errors.unknown_error'));
            } else {
                self::writeLog($sync->type, $message);
            }
        }

        return $exception->getMessage();
    }

    public static function setFatalError($sync)
    {
        $sync->fatal_error = 1;
        $sync->save();
    }

    public static function break($job_id)
    {
        \DB::table('jobs')->where('id', $job_id)->delete();
    }

    public static function getSync($sync_id)
    {
        try {
            $sync = Sync::findOrFail($sync_id);
        } catch (ModelNotFoundException $exception) {
            throw new ModelNotFoundException();
        }

        return $sync;
    }

    public static function writeLog($dataType, $message, $sync = null)
    {
        $path = "/files/" . config('syncs.moysklad.externalCode') . '/' . $dataType . '.log';

        Storage::disk(config('lfm.disk'))->append($path, $message);

        if (!is_null($sync)) {
            $sync->warnings_count++;
            $sync->update();
        }
    }

    public static function deleteLog($dataType)
    {
        $path = "/files/" . config('syncs.moysklad.externalCode') . '/' . $dataType . '.log';

        Storage::disk(config('lfm.disk'))->delete($path);
    }

    public static function createSync($dataType = null)
    {
        $sync = new Sync();
        $sync->data_type = config('syncs.moysklad.externalCode');
        $sync->manually = true;
        $sync->type = $dataType;

        return $sync;
    }

    public static function getUpdateParameters($action, $dataType, $sync)
    {
        $parameters = [];
        $externalApi = ExternalApi::where('code', config('syncs.moysklad.externalCode'))->first();
        if (array_key_exists($dataType, $externalApi->settings) && array_key_exists('update_parameters', $externalApi->settings[$dataType])) {
            $parameters = $externalApi->settings[$dataType]['update_parameters'][$action] ?? [];
        }

        return $parameters;
    }
}
