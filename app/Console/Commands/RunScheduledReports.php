<?php

namespace App\Console\Commands;

use App\Models\ScheduledReport;
use App\Services\ScheduledReportRunner;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class RunScheduledReports extends Command
{
    protected $signature = 'reports:run-scheduled';

    protected $description = 'Run due scheduled reports and record history';

    public function handle(ScheduledReportRunner $runner): int
    {
        $now = Carbon::now();
        $scheduled = ScheduledReport::query()
            ->with('report')
            ->where('status', 'active')
            ->where(function ($q) use ($now) {
                $q->whereNull('next_run_at')->orWhere('next_run_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
            })
            ->get();

        if ($scheduled->isEmpty()) {
            $this->info('No scheduled reports due.');

            return self::SUCCESS;
        }

        $ran = 0;
        $failed = 0;

        foreach ($scheduled as $sr) {
            if (! $runner->isDue($sr, $now)) {
                continue;
            }

            try {
                $run = $runner->execute($sr, null, false, true);
                if ($run->status === 'success') {
                    $ran++;
                    $this->info("Ran #{$sr->id} {$sr->name}");
                } else {
                    $failed++;
                    $this->error("Failed #{$sr->id} {$sr->name}: {$run->error_message}");
                }
            } catch (Throwable $e) {
                $failed++;
                Log::error('Scheduled report command failed', [
                    'id' => $sr->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("Failed #{$sr->id}: {$e->getMessage()}");
            }
        }

        $this->info("Scheduled reports processed. success={$ran} failed={$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
