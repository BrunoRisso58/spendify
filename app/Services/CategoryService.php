<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{
    public function list()
    {
        return Category::select('name', 'type', 'user_id')->get();
    }

    public function getByUser($userId)
    {
        return Category::where('user_id', $userId)
            ->select('name', 'type', 'user_id')
            ->get();
    }

    public function create($userId, $data)
    {
        $data['user_id'] = $userId;

        return Category::create($data);
    }

    public function update($userId, $id, $data)
    {
        $recurrence = Category::where('user_id', $userId)->findOrFail($id);
        $recurrence->update($data);

        return $recurrence;
    }

    public function delete($userId, $id)
    {
        $recurrence = Category::where('user_id', $userId)->findOrFail($id);
        $recurrence->delete();
    }
}
