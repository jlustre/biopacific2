<?php

namespace Tests\Feature;

use App\Models\BPEmployee;
use App\Models\Facility;
use App\Models\Upload;
use App\Models\UploadType;
use App\Models\User;
use App\Services\MemberDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class MemberDocumentsFilterPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_documents_page_supports_search_status_filter_and_pagination(): void
    {
        $user = User::factory()->create();
        $facility = Facility::query()->create([
            'name' => 'Docs Facility',
            'slug' => 'docs-facility',
            'color_scheme_id' => null,
        ]);
        $employee = BPEmployee::query()->create([
            'employee_num' => 'EMP-DOCS-001',
            'user_id' => $user->id,
            'first_name' => 'Docs',
            'last_name' => 'Employee',
            'email' => $user->email,
        ]);
        $type = UploadType::query()->create([
            'name' => 'Driver License',
            'requires_expiry' => true,
            'is_license_or_certification' => false,
        ]);

        foreach (range(1, 12) as $index) {
            Upload::query()->create([
                'facility_id' => $facility->id,
                'employee_num' => $employee->employee_num,
                'user_id' => $user->id,
                'upload_type_id' => $type->id,
                'file_path' => "employee_documents/{$employee->employee_num}/file-{$index}.pdf",
                'original_filename' => $index === 3 ? 'special-license.pdf' : "document-{$index}.pdf",
                'uploaded_at' => now()->subDays($index),
                'verification_status' => $index <= 2
                    ? Upload::VERIFICATION_PENDING
                    : Upload::VERIFICATION_APPROVED,
            ]);
        }

        $service = app(MemberDashboardService::class);

        $page = $service->buildDocumentsPage($user, Request::create('/dashboard/documents', 'GET', [
            'per_page' => 5,
            'page' => 1,
        ]));
        $paginator = $page['documentsCenter']['documents_paginator'];
        $this->assertSame(12, $paginator->total());
        $this->assertSame(5, $paginator->perPage());
        $this->assertCount(5, $paginator->items());
        $this->assertTrue($paginator->hasPages());

        $searchPage = $service->buildDocumentsPage($user, Request::create('/dashboard/documents', 'GET', [
            'q' => 'special-license',
        ]));
        $this->assertSame(1, $searchPage['documentsCenter']['documents_paginator']->total());
        $this->assertSame(
            'special-license.pdf',
            $searchPage['documentsCenter']['documents'][0]['name'] ?? null
        );

        $statusPage = $service->buildDocumentsPage($user, Request::create('/dashboard/documents', 'GET', [
            'status' => Upload::VERIFICATION_PENDING,
        ]));
        $this->assertSame(2, $statusPage['documentsCenter']['documents_paginator']->total());

        $this->actingAs($user)
            ->get(route('member.documents', [
                'q' => 'special-license',
                'status' => 'approved',
                'per_page' => 10,
            ]))
            ->assertOk()
            ->assertSee('special-license.pdf', false)
            ->assertSee('Search', false)
            ->assertSee('Per page', false);
    }
}
