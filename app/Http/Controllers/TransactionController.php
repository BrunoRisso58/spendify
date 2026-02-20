<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\TransactionService;
use App\Traits\ApiResponse;

class TransactionController extends Controller
{
    use ApiResponse;

    public function __construct(private TransactionService $service) {}

    public function index(Request $request)
    {
        try {
            $transactions = $this->service->list(Auth::id(), $request->all());
            return $this->successResponse($transactions, 'Transactions retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve transactions: ' . $e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'amount' => 'required|numeric',
                'type' => 'required|in:entrada,saída',
                'date' => 'required|date',
                'description' => 'nullable|string',
                'category_id' => 'nullable|exists:categories,id',
            ]);

            $transaction = $this->service->create(Auth::id(), $data);
            return $this->successResponse($transaction, 'Transaction created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create transaction: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        return $this->successResponse(
            $this->service->find(Auth::id(), $id),
            'Transaction retrieved successfully'
        );
    }

    public function update(Request $request, $id)
    {
        return $this->successResponse(
            $this->service->update(Auth::id(), $id, $request->all()),
            'Transaction updated successfully'
        );
    }

    public function destroy($id)
    {
        $this->service->delete(Auth::id(), $id);

        return $this->successResponse(null, 'Transaction deleted successfully');
    }
}
