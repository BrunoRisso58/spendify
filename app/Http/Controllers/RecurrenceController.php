<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\RecurrenceService;

class RecurrenceController extends Controller
{
    public function __construct(private RecurrenceService $service) {}

    public function index()
    {
        return response()->json(
            $this->service->list(Auth::id())
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'type' => 'required|in:entrada,saída',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
            'interval' => 'integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        return response()->json(
            $this->service->create(Auth::id(), $data),
            201
        );
    }

    public function update(Request $request, $id)
    {
        return response()->json(
            $this->service->update(Auth::id(), $id, $request->all())
        );
    }

    public function destroy($id)
    {
        $this->service->delete(Auth::id(), $id);

        return response()->json(['message' => 'Deleted']);
    }
}
