<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recurrence extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'amount',
        'type',
        'description',
        'frequency',
        'interval',
        'start_date',
        'end_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
