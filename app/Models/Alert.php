<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'reference_id',
        'period',
        'date',
        'read'
    ];

    protected $casts = [
        'read' => 'boolean',
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
