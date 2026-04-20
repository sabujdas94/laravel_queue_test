<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>API Call Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .stat-card { border-left: 4px solid; }
        .stat-total  { border-color: #0d6efd; }
        .stat-completed { border-color: #198754; }
        .stat-pending   { border-color: #ffc107; }
        .stat-failed    { border-color: #dc3545; }
        .badge-pending    { background:#ffc107; color:#000; }
        .badge-processing { background:#0dcaf0; color:#000; }
        .badge-completed  { background:#198754; }
        .badge-failed     { background:#dc3545; }
        .response-body { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block; }
        pre.payload-preview { max-height: 80px; overflow: auto; font-size: 11px; margin:0; background:#f8f9fa; padding:4px; border-radius:4px; }
    </style>
</head>
<body>
<div class="container-fluid py-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="mb-0 fw-bold">API Call Report</h4>
        <span class="text-muted small">{{ now()->format('d M Y, H:i') }}</span>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card stat-total h-100">
                <div class="card-body">
                    <div class="text-muted small">Total</div>
                    <div class="fs-3 fw-bold text-primary">{{ number_format($stats->total) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card stat-completed h-100">
                <div class="card-body">
                    <div class="text-muted small">Completed</div>
                    <div class="fs-3 fw-bold text-success">{{ number_format($stats->completed) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card stat-pending h-100">
                <div class="card-body">
                    <div class="text-muted small">Pending / Processing</div>
                    <div class="fs-3 fw-bold text-warning">{{ number_format($stats->pending + $stats->processing) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card stat-failed h-100">
                <div class="card-body">
                    <div class="text-muted small">Failed</div>
                    <div class="fs-3 fw-bold text-danger">{{ number_format($stats->failed) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('report') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small mb-1">Search (URL / API Key)</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Forward URL or API key name"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach(['pending','processing','completed','failed'] as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('report') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:60px">#</th>
                            <th>API Key</th>
                            <th>Forward URL</th>
                            <th>Payload</th>
                            <th>Status</th>
                            <th>HTTP</th>
                            <th>Response</th>
                            <th>Error</th>
                            <th>Created</th>
                            <th>Processed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td class="text-muted small">{{ $log->id }}</td>
                            <td class="small">{{ $log->api_key_name ?? '—' }}</td>
                            <td class="small">
                                <span title="{{ $log->forward_url }}" style="max-width:180px; display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                    {{ $log->forward_url }}
                                </span>
                            </td>
                            <td>
                                <pre class="payload-preview">{{ $log->payload }}</pre>
                            </td>
                            <td>
                                <span class="badge badge-{{ $log->status }}">{{ ucfirst($log->status) }}</span>
                            </td>
                            <td class="small text-center">
                                @if($log->response_status)
                                    <span class="badge {{ $log->response_status >= 200 && $log->response_status < 300 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $log->response_status }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($log->response_body)
                                    <span class="response-body small" title="{{ $log->response_body }}">
                                        {{ $log->response_body }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="small text-danger">
                                {{ $log->error_message ? \Illuminate\Support\Str::limit($log->error_message, 60) : '—' }}
                            </td>
                            <td class="small text-nowrap">{{ $log->created_at }}</td>
                            <td class="small text-nowrap">{{ $log->processed_at ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">No records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
        <div class="card-footer d-flex justify-content-between align-items-center">
            <span class="small text-muted">
                Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }} records
            </span>
            {{ $logs->links() }}
        </div>
        @endif
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
