<?php

namespace App\Services;

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
        $totalExits = $transactions->where('type', 'saída')->sum('amount');
        $netBalance = $transactions->sum(function ($transaction) {
            return $transaction->type === 'entrada' ? $transaction->amount : -$transaction->amount;
        });

        $summary = [
            'total_entries' => $totalEntries,
            'total_exits' => $totalExits,
            'net_balance' => $netBalance,
        ];

        return $summary;
    }

    public function getCategories($userId, $filters = [])
    {
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;

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

        $total = $transactions->sum('total');

        return $transactions->map(function ($item) use ($total) {
            return [
                'category_id' => $item->category_id,
                'category_name' => $item->category?->name,
                'type' => $item->type,
                'total' => (float) $item->total,
                'percentage' => $total > 0
                    ? round(($item->total / $total) * 100, 2)
                    : 0,
            ];
        });
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
