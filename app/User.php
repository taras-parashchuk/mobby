<?php

namespace App;

use App\Models\Setting;
use App\Notifications\MailResetPasswordNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group_id', 'firstname', 'lastname', 'email', 'password', 'telephone', 'newsletter'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'group_id' => 'integer',
        'newsletter' => 'boolean',
        'is_admin' => 'boolean'
    ];

    private static $group_id = null;

    public function wishlist()
    {
        return $this->hasManyThrough(
            'App\Models\Product',
            'App\Models\Wishlist',
            'user_id',
            'id',
            'id',
            'product_id'
        );
    }

    public function isAdmin(): bool
    {
        return $this->is_admin == true;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new MailResetPasswordNotification($token));
    }

    public static function getGroupId()
    {
        if(!self::$group_id){
            self::$group_id = auth()->user()->group_id ?? Setting::get('user_group_before_register');
        }

        return self::$group_id;
    }

    public function externalSource()
    {
        return $this->hasMany('App\Models\UserExternalSource', 'user_id', 'id');
    }

    public function getFullNameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }
}
