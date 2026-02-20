<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'amount',
        'type',
        'date',
        'description',
        'recurrence_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function recurrence()
    {
        return $this->belongsTo(Recurrence::class);
    }
}
