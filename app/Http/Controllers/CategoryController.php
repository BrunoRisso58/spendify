<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\CategoryService;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $service) {}

    public function index()
    {
        return response()->json(
            $this->service->list()
        );
    }

    public function getByAuthenticatedUser()
    {
        return response()->json(
            $this->service->getByUser(Auth::id())
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:entrada,saída',
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
