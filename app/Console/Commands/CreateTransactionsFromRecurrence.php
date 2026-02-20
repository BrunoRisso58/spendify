<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Recurrence;
use App\Models\Transaction;
use Carbon\Carbon;

class CreateTransactionsFromRecurrence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-transactions-from-recurrence';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create transactions based on active recurrences';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            Recurrence::where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now()->toDateString());
            })->chunk(100, function ($recurrences) {
                foreach ($recurrences as $recurrence) {
                    $start = Carbon::parse($recurrence->start_date);
                    $now = now();
                    $current = null;

                    info($recurrence);

                    switch ($recurrence->frequency) {
                        case 'daily':
                            $current = $now->copy();
                            break;

                        case 'weekly':
                            $current = $now->startOfWeek()
                                ->addDays($start->dayOfWeek - 1);
                            break;

                        case 'monthly':
                            $current = $now->startOfMonth()
                                ->addDays(min(
                                    $start->day - 1,
                                    $now->daysInMonth - 1
                                ));
                            break;

                        case 'yearly':
                            $monthStart = $now->startOfYear()
                                ->addMonths($start->month - 1);

                            $current = $monthStart->copy()
                                ->addDays(min(
                                    $start->day - 1,
                                    $monthStart->daysInMonth - 1
                                ));
                            break;

                        default:
                            continue 2;
                    }

                    // Não cria se a data já passou
                    if ($current->lt($now->startOfDay())) {
                        continue;
                    }

                    Transaction::firstOrCreate(
                        [
                            'recurrence_id' => $recurrence->id,
                            'date' => $current->toDateString(),
                        ],
                        [
                            'title' => $recurrence->title,
                            'amount' => $recurrence->amount,
                            'type' => $recurrence->type,
                            'date' => $current->toDateString(),
                            'description' => $recurrence->description,
                            'category_id' => $recurrence->category_id,
                            'user_id' => $recurrence->user_id,
                        ]
                    );
                }
            });

            $this->info('Transactions generated successfully from active recurrences.');
        } catch (\Exception $e) {
            $this->error('Error creating transactions from recurrence: ' . $e->getMessage());
        }
    }
}
