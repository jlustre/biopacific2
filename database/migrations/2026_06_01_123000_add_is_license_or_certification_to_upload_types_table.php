<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('upload_types', function (Blueprint $table) {
            $table->boolean('is_license_or_certification')->default(false)->after('requires_expiry');
        });

        DB::table('upload_types')
            ->select('id', 'name')
            ->orderBy('id')
            ->chunk(200, function ($rows): void {
                foreach ($rows as $row) {
                    $name = is_string($row->name) ? $row->name : '';
                    $haystack = strtolower(trim($name));

                    $isLicenseOrCertification = preg_match('/license|licensure|certification|credential|cpr|bls|acls|rn\b|lpn\b|lvn\b/', $haystack) === 1;

                    DB::table('upload_types')
                        ->where('id', $row->id)
                        ->update(['is_license_or_certification' => $isLicenseOrCertification]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('upload_types', function (Blueprint $table) {
            $table->dropColumn('is_license_or_certification');
        });
    }
};
