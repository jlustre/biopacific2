<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\ImportLog;
use App\Services\ImportLogReverter;
use App\Support\ImportMappingPresetAccess;
use Illuminate\Http\Request;

class ImportLogAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|super-admin']);
    }

    public function index(Request $request)
    {
        $globalId = (int) config('import-mapping.global_facility_id', 99);

        $query = ImportLog::query()
            ->with(['user:id,name,email', 'facility:id,name', 'preset:id,name'])
            ->withCount('changes');

        if ($request->filled('facility_id')) {
            $query->where('facility_id', (int) $request->facility_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('preset_id')) {
            $query->where('import_mapping_preset_id', (int) $request->preset_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('source_filename', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('preset', fn ($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }

        $logs = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        $facilities = Facility::orderBy('name')->get(['id', 'name']);

        return view('admin.import-logs.index', compact('logs', 'facilities', 'globalId'));
    }

    public function show(ImportLog $importLog)
    {
        $importLog->load([
            'user:id,name,email',
            'facility:id,name',
            'preset:id,name',
            'revertedByUser:id,name,email',
            'changes' => fn ($q) => $q->orderBy('table_name')->orderBy('employee_num'),
        ]);

        $changeStats = $importLog->changes()
            ->selectRaw('table_name, action, count(*) as total')
            ->groupBy('table_name', 'action')
            ->get();

        return view('admin.import-logs.show', compact('importLog', 'changeStats'));
    }

    public function revert(ImportLog $importLog, ImportLogReverter $reverter)
    {
        if (!ImportMappingPresetAccess::canUse()) {
            return back()->with('error', 'You do not have permission to revert imports.');
        }

        try {
            $result = $reverter->revert($importLog);

            $message = sprintf(
                'Import reverted. %d record(s) restored, %d inserted record(s) removed.',
                $result['stats']['restored'],
                $result['stats']['deleted']
            );

            if (!empty($result['errors'])) {
                $message .= ' Some changes could not be reverted — see the import log for details.';
            }

            return redirect()->route('admin.import-logs.show', $importLog)
                ->with('success', $message);
        } catch (\Throwable $e) {
            return back()->with('error', 'Revert failed: ' . $e->getMessage());
        }
    }
}
