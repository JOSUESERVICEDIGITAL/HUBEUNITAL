<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospect_pdf_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained('public_service_prospects')->restrictOnDelete();
            $table->foreignId('downloaded_by')->constrained('users')->restrictOnDelete();
            $table->string('file_path')->unique();
            $table->string('file_name');
            $table->timestamp('downloaded_at')->index();
            $table->timestamps();
            $table->index(['prospect_id', 'downloaded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_pdf_downloads');
    }
};
