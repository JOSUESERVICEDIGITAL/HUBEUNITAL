<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rh_leaves', function (Blueprint $table) {
            $table->string('commercial_opinion')->default('pending');
            $table->text('commercial_notes')->nullable();
            $table->foreignId('commercial_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }
    public function down(): void
    {
        Schema::table('rh_leaves', function (Blueprint $table) {
            $table->dropForeign(['commercial_reviewed_by']);
            $table->dropColumn(['commercial_opinion', 'commercial_notes', 'commercial_reviewed_by']);
        });
    }
};
