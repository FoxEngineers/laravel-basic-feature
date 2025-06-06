<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Return a standardized API response.
     *
     * @param  mixed|null  $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function apiResponse(bool $success = true, string $message = '', mixed $data = null, int $status = 200)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}
