<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Prasarana;
use App\Models\Sarana;
use App\Models\User;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->middleware(['auth', 'user.not.blocked', 'profile.completed']);
        $this->middleware('permission:report.view')->only(['index']);
        $this->middleware('permission:report.export')->only(['export']);

        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $filters = $this->prepareFilters($request);

        $reportData = $this->reportService->getPeminjamanReportData($filters, (int) $request->input('per_page', 15));
        $analytics = auth()->user()->can('analytics.view')
            ? $this->reportService->getTopSaranaUsage($filters)
            : [];

        $statusOptions = [
            Peminjaman::STATUS_PENDING => 'Pending',
            Peminjaman::STATUS_APPROVED => 'Approved',
            Peminjaman::STATUS_REJECTED => 'Rejected',
            Peminjaman::STATUS_PICKED_UP => 'Picked Up',
            Peminjaman::STATUS_RETURNED => 'Returned',
            Peminjaman::STATUS_CANCELLED => 'Cancelled',
        ];

        $users = User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->limit(100)
            ->get();

        $saranaList = Sarana::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        $prasaranaList = Prasarana::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        return view('reports.index', [
            'paginator' => $reportData['paginator'],
            'summary' => $reportData['summary'],
            'filters' => $filters,
            'statusOptions' => $statusOptions,
            'users' => $users,
            'saranaList' => $saranaList,
            'prasaranaList' => $prasaranaList,
            'analytics' => $analytics,
        ]);
    }

    public function export(Request $request)
    {
        $filters = $this->prepareFilters($request);
        $rows = $this->reportService->getPeminjamanRowsForExport($filters);
        $summary = $this->reportService->summarizeCollection($rows);

        $filename = 'laporan-peminjaman-' . Carbon::parse($filters['start_date'])->format('Ymd') . '-' . Carbon::parse($filters['end_date'])->format('Ymd') . '.pdf';

        $pdf = Pdf::loadView('reports.pdf', [
            'rows' => $rows,
            'summary' => $summary,
            'filters' => $filters,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    protected function prepareFilters(Request $request): array
    {
        $defaultEnd = Carbon::now()->endOfDay();
        $defaultStart = $defaultEnd->copy()->subMonth()->startOfDay();

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        try {
            $start = $startDate ? Carbon::parse($startDate)->startOfDay() : $defaultStart;
        } catch (\Throwable $e) {
            $start = $defaultStart;
        }

        try {
            $end = $endDate ? Carbon::parse($endDate)->endOfDay() : $defaultEnd;
        } catch (\Throwable $e) {
            $end = $defaultEnd;
        }

        if ($end->lessThan($start)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        $filters = [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'status' => $request->input('status'),
            'user_id' => $request->input('user_id'),
            'search' => $request->input('search'),
            'sarpras_id' => $request->input('sarpras_id'),
        ];

        $filtered = Arr::where($filters, static fn($value, $key) => !is_null($value) && $value !== '');

        $sarprasType = $request->input('sarpras_type', $request->input('sarpras_id') ? 'sarana' : null);
        if (!empty($sarprasType)) {
            $filtered['sarpras_type'] = $sarprasType;
        }

        return $filtered;
    }
}
