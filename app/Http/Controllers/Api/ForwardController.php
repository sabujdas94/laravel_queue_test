<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ForwardRequestJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ForwardController extends Controller
{
    /**
     * Handle incoming forward request
     */
    public function forward(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'forward_url' => 'required|url',
            'header' => 'nullable|array',
            'payload' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            // Create request log entry
            $requestLogId = DB::table('request_logs')->insertGetId([
                'api_key_id' => $request->input('api_key_id'),
                'forward_url' => $request->input('forward_url'),
                'payload' => json_encode($request->input('payload')),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update last used timestamp for API key
            DB::table('api_keys')
                ->where('id', $request->input('api_key_id'))
                ->update(['last_used_at' => now()]);

            // Dispatch job to queue
            ForwardRequestJob::dispatch(
                $requestLogId,
                $request->input('forward_url'),
                $request->input('payload'),
                $request->input('header', [])
            );

            return response()->json([
                'success' => true,
                'message' => 'Request queued successfully',
                'request_id' => $requestLogId
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to queue request',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get request status
     */
    public function status($requestId)
    {
        $log = DB::table('request_logs')
            ->where('id', $requestId)
            ->first();

        if (!$log) {
            return response()->json([
                'error' => 'Request not found'
            ], 404);
        }

        return response()->json([
            'request_id' => $log->id,
            'status' => $log->status,
            'forward_url' => $log->forward_url,
            'response_status' => $log->response_status,
            'error_message' => $log->error_message,
            'created_at' => $log->created_at,
            'processed_at' => $log->processed_at,
        ]);
    }
}
