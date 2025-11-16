<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Consumer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'consumers';
    protected $primaryKey = 'consumer_id';
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
        'fname',
        'lname',
        'mname',
        'email',
        'phone_no',
        'address',
        'profile_image',
        'username',
        'password',
        'status',
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
     * Get the full name attribute.
     */
    public function getFullNameAttribute()
    {
        return trim($this->fname . ' ' . ($this->mname ? $this->mname . ' ' : '') . $this->lname);
    }

    /**
     * Get the user type.
     */
    public function getUserTypeAttribute()
    {
        return 'consumer';
    }
}
