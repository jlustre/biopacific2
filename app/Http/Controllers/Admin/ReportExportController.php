<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReportExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportExportController extends Controller
{
    public function show(Request $request, ReportExport $reportExport)
    {
        $this->authorizeOwner($request, $reportExport);
        $reportExport->loadMissing('report');

        return view('admin.reports.export-status', compact('reportExport'));
    }

    public function status(Request $request, ReportExport $reportExport)
    {
        $this->authorizeOwner($request, $reportExport);

        return response()->json([
            'status' => $reportExport->status,
            'row_count' => $reportExport->row_count,
            'error_message' => $reportExport->error_message,
            'download_url' => $reportExport->status === ReportExport::STATUS_COMPLETED
                ? route('admin.reports.exports.download', $reportExport)
                : null,
        ]);
    }

    public function download(Request $request, ReportExport $reportExport)
    {
        $this->authorizeOwner($request, $reportExport);
        abort_unless($reportExport->status === ReportExport::STATUS_COMPLETED, 409);
        abort_unless(
            $reportExport->file_path && Storage::disk('local')->exists($reportExport->file_path),
            404
        );

        return Storage::disk('local')->download(
            $reportExport->file_path,
            $reportExport->file_name ?: 'report.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    private function authorizeOwner(Request $request, ReportExport $reportExport): void
    {
        abort_unless((int) $reportExport->user_id === (int) $request->user()?->id, 403);
    }
}
