<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ReportService;
use App\Traits\ApiResponse;

class ReportController extends Controller
{
    use ApiResponse;

    public function __construct(private ReportService $service) {}

    public function summary(Request $request)
    {
        return response()->json(
            $this->service->getSummary(Auth::id(), $request->all())
        );
    }

    public function categories(Request $request)
    {
        return response()->json(
            $this->service->getCategories(Auth::id(), $request->all())
        );
    }

    public function cashflow(Request $request)
    {
        return response()->json(
            $this->service->getCashflow(Auth::id(), $request->all())
        );
    }
}
