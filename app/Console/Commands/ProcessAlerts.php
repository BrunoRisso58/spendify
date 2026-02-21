<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\Recurrence;
use App\Models\Alert;

class ProcessAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process financial alerts and send notifications to users.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $now = now();

            User::chunk(100, function ($users) use ($now) {
                foreach ($users as $user) {                
                    $this->processBudgetAlerts($user, $now);
                    $this->processBalanceAlerts($user, $now);
                    $this->processRecurrenceAlerts($user, $now);
                }
            });

            $this->info('Alerts processed successfully.');
        } catch (\Exception $e) {
            $this->error('Error processing alerts: ' . $e->getMessage());
            return;
        }
    }

    public function processBudgetAlerts($user, Carbon $now)
    {
        $budgets = Budget::where('user_id', $user->id)->get();

        foreach ($budgets as $budget) {
            $spent = Transaction::where('user_id', $user->id)
                ->where('category_id', $budget->category_id)
                ->whereBetween('date', [
                    $now->copy()->startOfMonth(),
                    $now->copy()->endOfMonth()
                ])
                ->sum('amount');

            // 80%
            if ($spent >= ($budget->limit * 0.8)) {
                Alert::firstOrCreate([
                    'user_id' => $user->id,
                    'type' => 'budget_warning',
                    'reference_id' => $budget->id,
                    'period' => $now->format('Y-m'),
                ]);
            }

            // 80%
            if ($spent >= $budget->limit) {
                Alert::firstOrCreate([
                    'user_id' => $user->id,
                    'type' => 'budget_exceeded',
                    'reference_id' => $budget->id,
                    'period' => $now->format('Y-m'),
                ]);
            }
        }
    }

    public function processBalanceAlerts($user, Carbon $now)
    {
        $balance = Transaction::where('user_id', $user->id)
            ->selectRaw("
                SUM(CASE 
                        WHEN type = 'entrada' THEN amount 
                        ELSE -amount 
                    END) as balance
            ")
            ->value('balance') ?? 0;

        if ($balance < 0) {
            Alert::firstOrCreate([
                'user_id' => $user->id,
                'type' => 'negative_balance',
                'period' => $now->toDateString(),
            ]);
        }
    }

    public function processRecurrenceAlerts($user, Carbon $now)
    {
        $recurrences = Recurrence::where('user_id', $user->id)->get();

        foreach ($recurrences as $recurrence) {
            $nextDate = $this->getNextOccurrence($recurrence, $now);

            if (!$nextDate) {
                continue;
            }

            if ($nextDate->isTomorrow()) {
                Alert::firstOrCreate([
                    'user_id' => $user->id,
                    'type' => 'recurrence_due',
                    'reference_id' => $recurrence->id,
                    'date' => $nextDate->toDateString(),
                ]);
            }
        }
    }

    private function getNextOccurrence($recurrence, Carbon $now)
    {
        $start = Carbon::parse($recurrence->start_date);

        if ($recurrence->frequency === 'daily') {
            return $now->copy()->addDay();
        }

        if ($recurrence->frequency === 'weekly') {
            return $start->copy()->setWeek(
                $now->week
            )->next($start->dayOfWeek);
        }

        if ($recurrence->frequency === 'monthly') {
            return $start->copy()->setDate(
                $now->year,
                $now->month,
                $start->day
            );
        }

        if ($recurrence->frequency === 'yearly') {
            return $start->copy()->setDate(
                $now->year,
                $start->month,
                $start->day
            );
        }

        return null;
    }
}
