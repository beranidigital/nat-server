<?php

namespace App\Http\Controllers;

use App\Models\Pool\StateLog;
use Illuminate\Http\Request;

class StateLogController extends Controller
{

    public function store(Request $request)
    {
        $authHeader = $request->header('Authorization') ?? $request->header('real');
        $authHeader = trim($authHeader);
        if (trim(config('auth.api_authorization')) !== $authHeader) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        // sensors must be an associative array with
        // device should be string
        $data = $request->validate([
            'device' => 'required|string',
            'friendly_name' => 'required|string',
            'sensors' => 'required|array',
            'attributes' => 'required|array',
        ]);

        $data = array_merge($data, [
            'ip_address' => $request->ip(),
            'headers' => $request->header(),
        ]);

        StateLog::create($data);

        return response()->json([
            'message' => 'State log created',
        ]);
    }
}
