<?php

namespace App\Services;

use App\Models\Transaction;

class TransactionService
{
    public function list($userId, $filters = [])
    {
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;
        $type = $filters['type'] ?? null;
        $categoryId = $filters['category_id'] ?? null;
        $search = $filters['search'] ?? null;
        $status = $filters['status'] ?? null;
        $recurrenceId = $filters['recurrence_id'] ?? null;

        $query = Transaction::where('user_id', $userId);

        if ($from) {
            $query->where('date', '>=', $from);
        }

        if ($to) {
            $query->where('date', '<=', $to);
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($search) {
            $query->where('title', 'like', "%$search%");
        }

        if ($status === 'pending') {
            $query->whereNull('paid_at');
        } 
        
        if ($status === 'paid') {
            $query->whereNotNull('paid_at');
        }

        if ($recurrenceId) {
            $query->where('recurrence_id', $recurrenceId);
        }

        return $query->latest('date')->get();
    }

    public function create($userId, $data)
    {
        $data['user_id'] = $userId;

        return Transaction::create($data);
    }

    public function find($userId, $id)
    {
        return Transaction::where('user_id', $userId)->findOrFail($id);
    }

    public function update($userId, $id, $data)
    {
        $transaction = $this->find($userId, $id);
        $transaction->update($data);

        return $transaction;
    }

    public function delete($userId, $id)
    {
        $transaction = $this->find($userId, $id);
        $transaction->delete();
    }

    public function pay($userId, $id)
    {
        $transaction = $this->find($userId, $id);
        
        $transaction->update([
            'paid_at' => now()
        ]);

        return $transaction;
    }

    public function unpay($userId, $id)
    {
        $transaction = $this->find($userId, $id);
        
        $transaction->update([
            'paid_at' => null
        ]);

        return $transaction;
    }
}
