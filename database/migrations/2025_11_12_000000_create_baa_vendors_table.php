<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('baa_vendors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facility_id')->nullable();
            $table->string('vendor_service');
            $table->string('type');
            $table->string('ephi_access');
            $table->string('baa_status');
            $table->string('notes')->nullable();
            $table->string('baa_form_path')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('baa_vendors');
    }
};
