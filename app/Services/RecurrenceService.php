<?php

namespace App\Services;

use App\Models\Recurrence;

class RecurrenceService
{
    public function list($userId)
    {
        return Recurrence::where('user_id', $userId)->get();
    }

    public function create($userId, $data)
    {
        $data['user_id'] = $userId;

        return Recurrence::create($data);
    }

    public function update($userId, $id, $data)
    {
        $recurrence = Recurrence::where('user_id', $userId)->findOrFail($id);
        $recurrence->update($data);

        return $recurrence;
    }

    public function delete($userId, $id)
    {
        $recurrence = Recurrence::where('user_id', $userId)->findOrFail($id);
        $recurrence->delete();
    }
}
