<?php

namespace App\Http\Controllers\Api;

use App\Models\Mpin;
use Illuminate\Http\Request;
use App\Traits\ResponseHandler;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MpinController extends Controller
{
    use ResponseHandler;

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mpin' => 'required|string|min:4|max:6|regex:/^[0-9]+$/',
        ]);

        if ($validator->fails()) {
            return $this->response(422, $validator->errors()->first(), [], false);
        }

        try {
            $user = $request->user();

            if ($user->mpin) {
                return $this->response(200, 'MPIN already created.', [], false);
            }

            $mpin = Mpin::updateOrCreate(
                ['user_id' => $request->user()->id],
                [
                    'mpin' => Hash::make($request->mpin),
                    'is_active' => true,
                ]
            );

            $data = $mpin->only(['id', 'user_id', 'is_active', 'created_at', 'updated_at']);

            return $this->response(200, 'MPIN created successfully.', $data, true);
        } catch (\Throwable $th) {
            return $this->response(500, $th->getMessage(), [], false);
        }
    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mpin' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->response(422, $validator->errors()->first(), [], false);
        }

        try {
            $mpin = Mpin::where('user_id', $request->user()->id)->where('is_active', true)->first();

            if (!$mpin) {
                return $this->response(404, 'MPIN not found. Please create one first.', [], false);
            }

            if (!Hash::check($request->mpin, $mpin->mpin)) {
                return $this->response(422, 'Invalid MPIN.', [], false);
            }

            $mpin->update(['last_used_at' => now()]);

            $data = $mpin->only(['id', 'user_id', 'last_used_at']);

            return $this->response(200, 'MPIN verified successfully.', $data, true);
        } catch (\Throwable $th) {
            return $this->response(500, $th->getMessage(), [], false);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_mpin' => 'required|string',
            'new_mpin' => 'required|string|min:4|max:6|regex:/^[0-9]+$/',
        ]);

        if ($validator->fails()) {
            return $this->response(422, $validator->errors()->first(), [], false);
        }

        try {
            $mpin = Mpin::where('user_id', $request->user()->id)->first();

            if (!$mpin) {
                return $this->response(404, 'MPIN not found.', [], false);
            }

            if (!Hash::check($request->old_mpin, $mpin->mpin)) {
                return $this->response(422, 'Invalid old MPIN.', [], false);
            }

            $mpin->update([
                'mpin' => Hash::make($request->new_mpin),
            ]);

            $data = $mpin->only(['id', 'user_id', 'updated_at']);

            return $this->response(200, 'MPIN updated successfully.', $data, true);
        } catch (\Throwable $th) {
            return $this->response(500, $th->getMessage(), [], false);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $mpin = Mpin::where('user_id', $request->user()->id)->first();

            if (!$mpin) {
                return $this->response(404, 'MPIN not found.', [], false);
            }

            $mpin->update(['is_active' => false]);

            return $this->response(200, 'MPIN deactivated successfully', [], true);
        } catch (\Throwable $th) {
            return $this->response(500, $th->getMessage(), [], false);
        }
    }
}
