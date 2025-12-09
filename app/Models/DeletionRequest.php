<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Consumer;
use App\Models\Establishment;
use App\Models\Foodbank;

class DeletionRequest extends Model
{
    use HasFactory;

    protected $table = 'account_deletion_requests';

    protected $fillable = [
        'user_id',
        'user_type',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'admin_notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    // Get user model based on user_type
    public function getUser()
    {
        return match($this->user_type) {
            'consumer' => Consumer::find($this->user_id),
            'establishment' => Establishment::find($this->user_id),
            'foodbank' => Foodbank::find($this->user_id),
            default => null
        };
    }
}

