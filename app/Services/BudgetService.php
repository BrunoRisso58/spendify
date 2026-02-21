<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Transaction;

class BudgetService
{
    public function list($userId, $filters = [])
    {
        return Budget::where('user_id', $userId)
            ->with('category:id,name')
            ->get();
    }

    public function create($userId, $data)
    {
        $data['user_id'] = $userId;

        return Budget::create($data);
    }

    public function find($userId, $id)
    {
        return Budget::where('user_id', $userId)
            ->with('category:id,name')
            ->findOrFail($id);
    }

    public function update($userId, $id, $data)
    {
        $budget = $this->find($userId, $id);
        $budget->update($data);

        return $budget;
    }

    public function delete($userId, $id)
    {
        $budget = $this->find($userId, $id);
        $budget->delete();
    }

    public function status($userId)
    {
        $budgets = Budget::where('user_id', $userId)
            ->with('category:id,name')
            ->get();

        return $budgets->map(function ($budget) use ($userId) {
            [$start, $end] = match ($budget->period) {
                'weekly' => [now()->startOfWeek(), now()->endOfWeek()],
                'yearly' => [now()->startOfYear(), now()->endOfYear()],
                default => [now()->startOfMonth(), now()->endOfMonth()],
            };

            $query = Transaction::where('user_id', $userId)
                ->where('type', 'saída')
                ->whereBetween('date', [$start, $end]);

            if ($budget->category_id) {
                $query->where('category_id', $budget->category_id);
            }

            $spent = (float) $query->sum('amount');

            $percentage = $budget->amount > 0
                ? ($spent / $budget->amount) * 100
                : 0;

            return [
                'budget_id' => $budget->id,
                'category' => $budget->category?->name,
                'limit' => (float) $budget->amount,
                'spent' => $spent,
                'remaining' => $budget->amount - $spent,
                'percentage_used' => round($percentage, 2),
                'status' => match (true) {
                    $percentage > 100 => 'exceeded',
                    $percentage == 100 => 'full',
                    $percentage >= 70 => 'warning',
                    default => 'safe'
                }
            ];
        });
    }
}
