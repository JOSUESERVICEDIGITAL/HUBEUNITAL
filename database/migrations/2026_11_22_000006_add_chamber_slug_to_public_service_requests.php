<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_service_requests', function (Blueprint $table) {
            $table->string('chamber_slug')->after('id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('public_service_requests', function (Blueprint $table) {
            $table->dropIndex(['chamber_slug']);
            $table->dropColumn('chamber_slug');
        });
    }
};
