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

    public function getNextInstallmentAttribute()
    {
        $transaction = $this->transactions()
            ->select('installment_number')
            ->orderByDesc('installment_number')
            ->first();

        $installmentNumber = $transaction->installment_number ?? $this->total_installments;

        return min($installmentNumber + 1, $this->total_installments);
    }
}
