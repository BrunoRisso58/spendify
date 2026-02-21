<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'total_amount',
        'total_installments',
        'current_installment',
        'start_date',
        'type',
        'description'
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getInstallmentValueAttribute()
    {
        return round($this->total_amount / $this->total_installments, 2);
    }

    public function getCurrentInstallmentAttribute()
    {
        $paidCount = $this->transactions()
            ->whereNotNull('paid_at')
            ->count();

        return min($paidCount + 1, $this->total_installments);
    }
}
