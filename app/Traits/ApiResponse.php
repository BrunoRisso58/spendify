<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\Response as ResponseStatus;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Generate a successful JSON response from the given parameters.
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function successResponse(mixed $data, string $message, int $code = ResponseStatus::HTTP_OK): JsonResponse
    {
        return new JsonResponse([
            'data' => $data,
            'message' => $message,
            'code' => $code
        ], $code);
    }

    /**
     * Generate an error JSON response from the given parameters.
     *
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function errorResponse(string $message, int $code): JsonResponse
    {
        return new JsonResponse([
            'message' => $message,
            'code' => $code
        ], $code);
    }
}
