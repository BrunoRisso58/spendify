<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AlertService;
use App\Traits\ApiResponse;

class AlertController extends Controller
{
    use ApiResponse;

    public function __construct(private AlertService $service) {}

    public function index(Request $request)
    {
        return response()->json(
            $this->service->list(Auth::id(), $request->all())
        );
    }

    public function toggle($id)
    {
        $alert = $this->service->toggle(Auth::id(), $id);

        return response()->json($alert);
    }
}
