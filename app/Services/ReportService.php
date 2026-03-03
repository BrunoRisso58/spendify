<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Transaction;

class ReportService
{
    public function getSummary($userId, $filters = [])
    {
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;

        $query = Transaction::where('user_id', $userId);

        if (isset($from)) {
            $query->whereDate('date', '>=', $from);
        }

        if (isset($to)) {
            $query->whereDate('date', '<=', $to);
        }

        $transactions = $query->get();

        $totalEntries = $transactions->where('type', 'entrada')->sum('amount');

        $totalExitsAll = $transactions->where('type', 'saída')->sum('amount');
        $totalExitsPaid = $transactions->where('type', 'saída')->whereNotNull('paid_at')->sum('amount');

        $netBalanceAll = $transactions->sum(function ($transaction) {
            return $transaction->type === 'entrada' ? $transaction->amount : -$transaction->amount;
        });
        $netBalancePaid = $transactions->whereNotNull('paid_at')->sum(function ($transaction) {
            return $transaction->type === 'entrada' ? $transaction->amount : -$transaction->amount;
        });

        $summary = [
            'total_entries' => $totalEntries,
            'total_exits' => [
                "all" => $totalExitsAll, 
                "paid" => $totalExitsPaid
            ],
            'net_balance' => [
                "all" => $netBalanceAll, 
                "paid" => $netBalancePaid
            ],
        ];

        return $summary;
    }

    public function getCategories($userId, $filters = [])
    {
        $from = $filters['from'] 
            ?? Carbon::now()->startOfMonth()->format('Y-m-d');

        $to = $filters['to'] 
            ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        $transactions = Transaction::query()
            ->selectRaw('category_id, type, SUM(amount) as total, MAX(date) as date')
            ->where('user_id', $userId)
            ->when(isset($from), function ($query) use ($from) {
                $query->whereDate('date', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('date', '<=', $to);
            })
            ->groupBy('category_id', 'type')
            ->with('category:id,name')
            ->get();

        // $total = $transactions->sum('total');
        $total['incomes'] = $transactions
            ->where('type', 'entrada')
            ->sum('total');

        $total['outcomes'] = $transactions
            ->where('type', 'saída')
            ->sum('total');

        $incomes = [];
        $outcomes = [];

        $transactions->map(function ($item) use ($total, &$incomes, &$outcomes) {
            if ($item->type == 'entrada') {
                // info($incomes);
                array_push($incomes, [
                    'category_id' => $item->category_id,
                    'category_name' => $item->category?->name,
                    'type' => $item->type,
                    'total' => (float) $item->total,
                    'percentage' => $total['incomes'] > 0 // change to total of type
                        ? round(($item->total / $total['incomes']) * 100, 2)
                        : 0,
                ]);
            } else {
                array_push($outcomes, [
                    'category_id' => $item->category_id,
                    'category_name' => $item->category?->name,
                    'type' => $item->type,
                    'total' => (float) $item->total,
                    'percentage' => $total['outcomes'] > 0 // change to total of type
                        ? round(($item->total / $total['outcomes']) * 100, 2)
                        : 0,
                ]);
            }
        });

        return [
            'incomes' => $incomes,
            'outcomes' => $outcomes
        ];
    }

    public function getCashflow($userId, $filters = [])
    {
        $period = $filters['period'] ?? 'month';

        switch ($period) {
            case 'week':
                $start = now()->startOfWeek();
                $end = now()->endOfWeek();
                $format = '%Y-%m-%d';
                break;

            case 'year':
                $start = now()->startOfYear();
                $end = now()->endOfYear();
                $format = '%Y-%m';
                break;

            case 'month':

            default:
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
                $format = '%Y-%m-%d';
                break;
        }

        $results = Transaction::query()
            ->selectRaw("
                DATE_FORMAT(date, '{$format}') as period,
                type,
                SUM(amount) as total
            ")
            ->where('user_id', $userId)
            ->whereBetween('date', [$start, $end])
            ->whereNotNull('paid_at')
            ->groupBy('period', 'type')
            ->orderBy('period')
            ->get();

        $typeMap = [
            'entrada' => 'income',
            'saida' => 'expense',
            'saída' => 'expense',
        ];

        $grouped = [];

        foreach ($results as $row) {
            $periodKey = $row->period;

            if (!isset($grouped[$periodKey])) {
                $grouped[$periodKey] = [
                    'period' => $periodKey,
                    'income' => 0,
                    'expense' => 0,
                ];
            }

            $type = $typeMap[$row->type] ?? $row->type;

            $grouped[$periodKey][$type] = (float) $row->total;
        }

        return array_values($grouped);
    }
}
