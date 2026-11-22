<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('service_key');
            $table->string('service_name');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('organization')->nullable();
            $table->text('details');
            $table->string('status')->default('new')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_service_requests');
    }
};
