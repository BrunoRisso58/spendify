<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\InstallmentService;
use App\Traits\ApiResponse;

class InstallmentController extends Controller
{
    use ApiResponse;

    public function __construct(private InstallmentService $service) {}

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'required|string',
            'total_amount' => 'required|numeric',
            'total_installments' => 'required|integer|min:2',
            'current_installment' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'type' => 'required|in:entrada,saída',
            'description' => 'nullable|string',
        ]);

        $data['user_id'] = Auth::id();

        return response()->json(
            $this->service->execute($data)
        );
    }
}
