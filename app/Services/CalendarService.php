<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Transaction;
use App\Models\Recurrence;

class CalendarService
{
    public function list($userId, $filters = [])
    {
        $month = $filters['month'] ?? now()->format('Y-m');
        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();
        $today = now()->startOfDay();

        // =========================
        // 🧾 TRANSACTIONS
        // =========================
        $transactions = Transaction::where('user_id', $userId)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'amount' => $t->amount,
                'type' => $t->type,
                'date' => Carbon::parse($t->date),
                'is_projected' => false,
                'is_paid' => $t->paid_at !== null,
            ]);

        // =========================
        // 🔁 RECORRÊNCIAS
        // =========================
        $recurrences = Recurrence::where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        $projected = [];

        foreach ($recurrences as $recurrence) {
            $dates = $this->generateOccurrencesForMonth($recurrence, $start, $end);

            foreach ($dates as $date) {
                if ($transactions->contains(fn ($t) => $t['date']->isSameDay($date) 
                        && $t['title'] === $recurrence->title 
                        && $t['amount'] == $recurrence->amount)
                    ) {
                    continue; // pular se já existe uma transação real para essa data + título + valor
                }

                $projected[] = [
                    'id' => null,
                    'title' => $recurrence->title,
                    'amount' => $recurrence->amount,
                    'type' => $recurrence->type,
                    'date' => $date,
                    'is_projected' => true,
                    'is_paid' => false, // pode ser usado para marcar como pago no futuro
                ];
            }
        }

        // =========================
        // 🔗 MERGE + SORT
        // =========================
        $all = collect($transactions)
            ->merge($projected)
            ->sortBy('date')
            ->values();

        // =========================
        // 💰 SALDO INICIAL
        // =========================
        $initialBalance = Transaction::where('user_id', $userId)
            ->where('date', '<', $start)
            ->selectRaw("
                SUM(CASE 
                    WHEN type = 'entrada' THEN amount 
                    ELSE -amount 
                END) as balance
            ")
            ->value('balance') ?? 0;

        $runningBalance = $initialBalance;

        // =========================
        // 📅 AGRUPAR POR DIA
        // =========================
        $calendar = [];

        foreach ($all as $item) {

            $dateKey = $item['date']->toDateString();

            // saldo acumulado
            // if ($item['type'] === 'entrada') {
            //     $runningBalance += $item['amount'];
            // } else {
            //     if ($item['is_paid']) {
            //         $runningBalance -= $item['amount'];
            //     }
            // }

            $runningBalance += $item['type'] === 'entrada'
                ? $item['amount']
                : -$item['amount'];

            // flags
            $isToday = $item['date']->isSameDay($today);
            $isOverdue = !$item['is_projected']
                ? false
                : $item['date']->lt($today);

            $formatted = [
                'id' => $item['id'],
                'title' => $item['title'],
                'amount' => $item['amount'],
                'type' => $item['type'],
                'is_projected' => $item['is_projected'],
                'is_today' => $isToday,
                'is_overdue' => $isOverdue,
                'is_paid' => $item['is_paid'],
            ];

            if (!isset($calendar[$dateKey])) {
                $calendar[$dateKey] = [
                    'date' => $dateKey,
                    'items' => [],
                    'daily_balance' => 0,
                    'running_balance' => 0,
                ];
            }

            $calendar[$dateKey]['items'][] = $formatted;

            // saldo do dia
            $calendar[$dateKey]['daily_balance'] += $item['type'] === 'entrada'
                ? $item['amount']
                : -$item['amount'];

            // saldo acumulado até o dia
            $calendar[$dateKey]['running_balance'] = (float) $runningBalance;
        }

        return [
            'data' => $calendar,
            'meta' => [
                'month' => $month,
                'initial_balance' => $initialBalance,
                'final_balance' => $runningBalance,
            ]
        ];
    }

    /**
     * Gera ocorrências dentro do mês
     */
    private function generateOccurrencesForMonth($recurrence, Carbon $start, Carbon $end)
    {
        $dates = [];
        $cursor = $start->copy();

        $recStart = Carbon::parse($recurrence->start_date);

        while ($cursor->lte($end)) {

            if ($cursor->lt($recStart)) {
                $cursor->addDay();
                continue;
            }

            switch ($recurrence->frequency) {

                case 'daily':
                    $dates[] = $cursor->copy();
                    break;

                case 'weekly':
                    if ($cursor->dayOfWeek === $recStart->dayOfWeek) {
                        $dates[] = $cursor->copy();
                    }
                    break;

                case 'monthly':
                    if ($cursor->day === $recStart->day) {
                        $dates[] = $cursor->copy();
                    }
                    break;

                case 'yearly':
                    if (
                        $cursor->day === $recStart->day &&
                        $cursor->month === $recStart->month
                    ) {
                        $dates[] = $cursor->copy();
                    }
                    break;
            }

            $cursor->addDay();
        }

        return $dates;
    }
}
