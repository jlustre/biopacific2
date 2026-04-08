<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('visibility')->default('admin'); // admin, all, role, facility
            $table->json('visible_roles')->nullable();
            $table->json('visible_facilities')->nullable();
        });
    }
    public function down()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['visibility', 'visible_roles', 'visible_facilities']);
        });
    }
};
