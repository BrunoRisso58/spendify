<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\CalendarService;
use App\Traits\ApiResponse;

class CalendarController extends Controller
{
    use ApiResponse;

    public function __construct(private CalendarService $service) {}

    public function index(Request $request)
    {
        return response()->json(
            $this->service->list(Auth::id(), $request->all())
        );
    }
}
