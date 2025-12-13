<?php

namespace App\Traits;

trait ResponseHandler
{
    public function response($status = 200, $message = 'Operation completed successfully.', $data = [], $success = true)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $status);
    }
}
