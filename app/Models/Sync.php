<?php

namespace App\Models;

use App\Helpers\FileManager;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/*
 * Vitaliy Sheverov, [19 бер. 2020 р., 09:28:58]:
при новому імпорті зачищати лог, кількість помилок, та успішні кількоті
при видаленні видаляти лог
при створенні добавити лог
при створенні нового ручного імпорту зачистити дані попереднього
добавити параметр finished, якщо автоматичний то зачистка
валідація згідно конфігурації, по наявності тегів

Vitaliy Sheverov, [19 бер. 2020 р., 09:34:07]:
добавити параметр fatal_error, якщо він є то статус не удалось або новий інтерфейс в ручному

Vitaliy Sheverov, [19 бер. 2020 р., 09:34:43]:
все решта то warnings


Якщо видалили конфігурацію, то писати помилку що конфігурація не знайдена, або не давати видалити конфігурацію, якщо вона юзається в імпорті
 */

class Sync extends Model
{
    //

    protected $appends = [
        'times',
    ];

    protected $casts = [
        'status' => 'boolean',
        'finished' => 'boolean',
        'manually' => 'boolean',
        'fatal_error' => 'boolean',
        'breaked' => 'boolean',
        'configuration_id' => 'integer',
        'current' => 'integer',
        'total' => 'integer',
        'paused' => 'boolean',
        'stopped' => 'boolean'
    ];

    public function getTimesAttribute()
    {
        $times = [
            $this->time_1
        ];

        if($this->time_2) $times[] = $this->time_1;

        return $times;
    }

    public function getConfigurationAttribute($value)
    {
        return json_decode($value);
    }

    public function getInfoAttribute($value)
    {
        return json_decode($value);
    }

    public function scopeAuto($q)
    {
        return $q->where('manually', false);
    }

    public function getLogFilePathAttribute()
    {
        if($this->manually){
            return "files/sync/xml/manually.log";
        }else{
            return "files/sync/xml/auto/{$this->id}.log";
        }
    }

    protected static function boot()
    {
        parent::boot();

        if(!app()->runningInConsole()){

            static::deleting(function($model){
               \Storage::disk('uploads')->delete($model->log_file_path);
            });

        }

        static::created(function ($model){
            if(\Storage::disk('uploads')->exists($model->log_file_path)){
                \Storage::disk('uploads')->delete($model->log_file_path);
            }
        });
    }
}
