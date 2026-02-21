<?php

namespace App\Console\Commands;

use App\Models\Installment;
use Illuminate\Console\Command;
use App\Models\Transaction;
use Carbon\Carbon;

class CreateTransactionsFromInstallments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-transactions-from-installments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create transactions based on active installments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            Installment::where('start_date', '<=', now()->toDateString())
              ->whereRaw('current_installment < total_installments')
              ->chunk(100, function ($installments) {
                foreach ($installments as $installment) {
                    $start = Carbon::parse($installment->start_date);
                    $now = now();
                    $current = null;

                    info($installment);

                    $current = $now->startOfMonth()
                        ->addDays(min(
                            $start->day - 1,
                            $now->daysInMonth - 1
                        ));

                    // Não cria se a data já passou
                    if ($current->lt($now->startOfDay())) {
                        continue;
                    }

                    $lastTransaction = Transaction::where('installment_id', $installment->id)
                        ->orderBy('installment_number', 'desc')
                        ->first();

                    $lastTransactionMonth = $lastTransaction ? Carbon::parse($lastTransaction->date)->month : null;
                    $currentMonth = $now->month;

                    if ($lastTransactionMonth === $currentMonth) {
                        continue;
                    }

                    Transaction::firstOrCreate(
                        [
                            'user_id' => $installment->user_id,
                            'installment_id' => $installment->id,
                            'installment_number' => $installment->current_installment,
                            'date' => $current->toDateString(),
                        ],
                        [
                            'user_id' => $installment->user_id,
                            'category_id' => $installment->category_id,
                            'installment_id' => $installment->id,
                            'installment_number' => $installment->current_installment,

                            'title' => $installment->title . " (" . $installment->current_installment . "/{$installment->total_installments})",
                            'amount' => $installment->installment_value,
                            'type' => $installment->type,

                            'date' => $current->toDateString(),

                            'description' => $installment->description,

                            'paid_at' => null,
                        ]
                    );
                }
            });

            $this->info('Transactions generated successfully from active installments.');
        } catch (\Exception $e) {
            $this->error('Error creating transactions from installment: ' . $e->getMessage());
        }
    }
}
