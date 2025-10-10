<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Establishment extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'establishments';
    protected $primaryKey = 'establishment_id';
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
        'business_name',
        'owner_fname',
        'owner_lname',
        'email',
        'phone_no',
        'address',
        'business_type',
        'bir_file',
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
     * Get the owner full name attribute.
     */
    public function getOwnerFullNameAttribute()
    {
        return trim($this->owner_fname . ' ' . $this->owner_lname);
    }

    /**
     * Get the user type.
     */
    public function getUserTypeAttribute()
    {
        return 'establishment';
    }

    /**
     * Get the food listings for this establishment.
     */
    public function foodListings(): HasMany
    {
        return $this->hasMany(FoodListing::class);
    }
}
