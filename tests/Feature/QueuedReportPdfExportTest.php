<?php

namespace Tests\Feature;

use App\Jobs\GenerateReportPdf;
use App\Models\Report;
use App\Models\ReportExport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QueuedReportPdfExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    }

    public function test_large_pdf_is_queued_instead_of_rendered_in_the_request(): void
    {
        Queue::fake();
        config(['reports.synchronous_pdf_row_limit' => 1]);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $report = $this->createReport();

        $response = $this->actingAs($admin)->get(route('admin.reports.download', [
            'report' => $report,
            'download' => 'pdf',
            'pdf_orientation' => 'landscape',
        ]));

        $export = ReportExport::query()->sole();
        $response->assertRedirect(route('admin.reports.exports.show', $export));
        $this->assertSame(2, $export->row_count);
        $this->assertSame('landscape', $export->pdf_orientation);
        Queue::assertPushed(
            GenerateReportPdf::class,
            fn (GenerateReportPdf $job) => $job->reportExportId === $export->id
        );
    }

    public function test_only_export_owner_can_poll_and_download_generated_pdf(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $owner->assignRole('admin');
        $otherAdmin = User::factory()->create();
        $otherAdmin->assignRole('admin');
        $report = $this->createReport();
        Storage::disk('local')->put('report-exports/test/report.pdf', 'pdf bytes');
        $export = ReportExport::query()->create([
            'report_id' => $report->id,
            'user_id' => $owner->id,
            'status' => ReportExport::STATUS_COMPLETED,
            'parameters' => [],
            'row_count' => 2,
            'file_path' => 'report-exports/test/report.pdf',
            'file_name' => 'test-report.pdf',
        ]);

        $this->actingAs($owner)
            ->get(route('admin.reports.exports.status', $export))
            ->assertOk()
            ->assertJsonPath('status', ReportExport::STATUS_COMPLETED);

        $this->actingAs($owner)
            ->get(route('admin.reports.exports.download', $export))
            ->assertOk()
            ->assertDownload('test-report.pdf');

        $this->actingAs($otherAdmin)
            ->get(route('admin.reports.exports.status', $export))
            ->assertForbidden();
    }

    public function test_queued_job_generates_and_stores_the_pdf(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $report = $this->createReport();
        $export = ReportExport::query()->create([
            'report_id' => $report->id,
            'user_id' => $admin->id,
            'status' => ReportExport::STATUS_QUEUED,
            'parameters' => [],
            'pdf_orientation' => 'landscape',
        ]);

        (new GenerateReportPdf($export->id))->handle();

        $export->refresh();
        $this->assertSame(ReportExport::STATUS_COMPLETED, $export->status);
        $this->assertSame(2, $export->row_count);
        $this->assertNotNull($export->file_path);
        Storage::disk('local')->assertExists($export->file_path);
    }

    private function createReport(): Report
    {
        return Report::query()->create([
            'name' => 'Large test report',
            'sql_template' => 'SELECT 1 AS result UNION ALL SELECT 2 AS result',
            'parameters' => [],
            'is_active' => true,
            'visibility' => 'admin',
        ]);
    }
}
