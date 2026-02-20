<?php

namespace App\Services;

use App\Models\Transaction;

class TransactionService
{
    public function list($userId, $filters = [])
    {
        $query = Transaction::where('user_id', $userId);

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['from'])) {
            $query->whereDate('date', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->whereDate('date', '<=', $filters['to']);
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
}
