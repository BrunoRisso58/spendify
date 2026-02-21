<?php

namespace App\Services;

use App\Models\Alert;

class AlertService
{
    public function list($userId, $filters = [])
    {
        $type = $filters['type'] ?? null;

        return Alert::where('user_id', $userId)
            ->when($type, function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->get();
    }

    public function toggle($userId, $id)
    {
        $alert = Alert::where('user_id', $userId)->findOrFail($id);
        $alert->read = !$alert->read;
        $alert->save();

        return $alert;
    }
}
