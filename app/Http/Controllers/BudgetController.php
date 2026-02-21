<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\BudgetService;
use App\Traits\ApiResponse;

class BudgetController extends Controller
{
    use ApiResponse;

    public function __construct(private BudgetService $service) {}

    public function index(Request $request)
    {
        return response()->json(
            $this->service->list(Auth::id(), $request->all())
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|in:weekly,monthly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        return response()->json(
            $this->service->create(Auth::id(), $data)
        );
    }

    public function show($id)
    {
        return response()->json(
            $this->service->find(Auth::id(), $id)
        );
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'amount' => 'sometimes|numeric|min:0',
            'period' => 'sometimes|in:weekly,monthly,yearly',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        return response()->json(
            $this->service->update(Auth::id(), $id, $data)
        );
    }

    public function destroy($id)
    {
        return response()->json(
            $this->service->delete(Auth::id(), $id)
        );
    }

    public function status()
    {
        return response()->json(
            $this->service->status(Auth::id())
        );
    }
}
