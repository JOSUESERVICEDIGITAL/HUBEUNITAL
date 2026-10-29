<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('social_media', function (Blueprint $table) {
            $table->string('bg_color')->default('#000000');
            $table->string('label')->default('Followers');
            $table->integer('count')->default(0);
        });
    }

    public function down(): void {
        Schema::table('social_media', function (Blueprint $table) {
            $table->dropColumn(['bg_color', 'label', 'count']);
        });
    }
};
