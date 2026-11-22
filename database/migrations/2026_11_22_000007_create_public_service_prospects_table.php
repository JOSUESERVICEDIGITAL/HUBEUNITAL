<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_service_prospects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('public_service_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('chamber_slug')->index();
            $table->string('service_key')->index();
            $table->string('service_name');
            $table->json('answers');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_service_prospects');
    }
};
