<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Installment;
use App\Models\Transaction;

class InstallmentService
{
    public function execute(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {

                $data['start_date'] = $data['start_date'] ?? Carbon::now()->toDateString();

                $installment = Installment::create([
                    'user_id' => $data['user_id'],
                    'category_id' => $data['category_id'] ?? null,
                    'title' => $data['title'],
                    'total_amount' => $data['total_amount'],
                    'total_installments' => $data['total_installments'],
                    'current_installment' => $data['current_installment'] ?? 1,
                    'start_date' => $data['start_date'],
                    'type' => $data['type'],
                    'description' => $data['description'] ?? null,
                ]);

                if (!$installment) {
                    throw new \Exception('Failed to create installment');
                }

                $value = round($data['total_amount'] / $data['total_installments'], 2);

                // past installment transactions
                for ($i = 1; $i <= $data['current_installment']; $i++) {
                    $paidAt = $i < $data['current_installment'] 
                        ? Carbon::now()->subMonths($data['current_installment'] - $i) 
                        : null;

                    Transaction::create([
                        'user_id' => $data['user_id'],
                        'category_id' => $installment->category_id,
                        'installment_id' => $installment->id,
                        'installment_number' => $installment->current_installment,

                        'title' => $data['title'] . " ({$installment->current_installment}/{$installment->total_installments})",
                        'amount' => $value,
                        'type' => $data['type'],

                        'date' => Carbon::parse($data['start_date'])->subMonths($data['current_installment'] - $i),

                        'description' => $installment->description,
                        'paid_at' => $paidAt,
                    ]);
                }

                return $installment;

            });
        } catch (\Exception $e) {
            throw new \Exception('Failed to create installment: ' . $e->getMessage());
        }
    }
}
