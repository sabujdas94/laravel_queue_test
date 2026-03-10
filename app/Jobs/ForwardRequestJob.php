<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ForwardRequestJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $requestLogId,
        public string $forwardUrl,
        public array $payload,
        public array $headers = []
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Update status to processing
            DB::table('request_logs')
                ->where('id', $this->requestLogId)
                ->update(['status' => 'processing']);

            // Prepare HTTP request
            $request = Http::timeout($this->timeout);

            // Add custom headers if provided
            if (!empty($this->headers)) {
                $request = $request->withHeaders($this->headers);
            }

            // Forward the request
            $response = $request->post($this->forwardUrl, $this->payload);

            // Log the response
            DB::table('request_logs')
                ->where('id', $this->requestLogId)
                ->update([
                    'status' => 'completed',
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                    'processed_at' => now(),
                ]);

            // Log::info('Request forwarded successfully', [
            //     'request_log_id' => $this->requestLogId,
            //     'forward_url' => $this->forwardUrl,
            //     'status' => $response->status(),
            // ]);

        } catch (\Exception $e) {
            // Log the error
            DB::table('request_logs')
                ->where('id', $this->requestLogId)
                ->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'processed_at' => now(),
                ]);

            Log::error('Failed to forward request', [
                'request_log_id' => $this->requestLogId,
                'forward_url' => $this->forwardUrl,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        DB::table('request_logs')
            ->where('id', $this->requestLogId)
            ->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'processed_at' => now(),
            ]);
    }
}
