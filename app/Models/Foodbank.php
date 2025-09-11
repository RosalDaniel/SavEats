<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Foodbank extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'foodbanks';
    protected $primaryKey = 'foodbank_id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'organization_name',
        'contact_person',
        'email',
        'phone_no',
        'address',
        'registration_number',
        'profile_image',
        'username',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'registered_at' => 'datetime',
        ];
    }

    /**
     * Get the user type.
     */
    public function getUserTypeAttribute()
    {
        return 'foodbank';
    }
}
