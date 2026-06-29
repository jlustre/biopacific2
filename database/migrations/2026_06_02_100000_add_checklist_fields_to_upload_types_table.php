<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upload_types', function (Blueprint $table) {
            $table->unsignedBigInteger('checklist_item_id')
                ->nullable()
                ->unique()
                ->after('description');

            $table->string('checklist_section', 16)
                ->nullable()
                ->after('checklist_item_id');

            $table->foreign('checklist_item_id')
                ->references('id')
                ->on('checklist_items')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('upload_types', function (Blueprint $table) {
            $table->dropForeign(['checklist_item_id']);
            $table->dropColumn(['checklist_item_id', 'checklist_section']);
        });
    }
};
