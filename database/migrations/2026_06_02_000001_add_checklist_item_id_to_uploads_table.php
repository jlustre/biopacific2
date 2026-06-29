<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->unsignedBigInteger('checklist_item_id')
                ->nullable()
                ->after('upload_type_id');

            $table->foreign('checklist_item_id')
                ->references('id')
                ->on('checklist_items')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->dropForeign(['checklist_item_id']);
            $table->dropColumn('checklist_item_id');
        });
    }
};
