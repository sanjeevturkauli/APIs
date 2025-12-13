<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

trait ResponseHandler
{
    public function success($status = 200, $message = 'Operation completed successfully.', $data = [], $success = true)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public function error($status = 200, $message = 'Operation completed successfully.', $data = [], $success = false)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $status);
    }
}
