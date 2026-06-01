<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('checklist_items', function (Blueprint $table) {
            $table->boolean('is_license_or_certification')->default(false)->after('isExpiring');
        });

        DB::table('checklist_items')
            ->leftJoin('doc_types', 'doc_types.id', '=', 'checklist_items.doc_type_id')
            ->select('checklist_items.id', 'checklist_items.name', 'doc_types.name as doc_type_name')
            ->orderBy('checklist_items.id')
            ->chunk(200, function ($rows): void {
                foreach ($rows as $row) {
                    $name = is_string($row->name) ? $row->name : '';
                    $docTypeName = is_string($row->doc_type_name) ? $row->doc_type_name : '';
                    $haystack = strtolower(trim($name . ' ' . $docTypeName));

                    $isLicenseOrCertification = preg_match('/license|licensure|certification|credential|cpr|bls|acls|rn\b|lpn\b|lvn\b/', $haystack) === 1;

                    DB::table('checklist_items')
                        ->where('id', $row->id)
                        ->update(['is_license_or_certification' => $isLicenseOrCertification]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('checklist_items', function (Blueprint $table) {
            $table->dropColumn('is_license_or_certification');
        });
    }
};
