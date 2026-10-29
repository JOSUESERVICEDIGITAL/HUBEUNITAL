<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::table('cei_supports', function (Blueprint $table) {
    $table->string('action1_url')->nullable();
    $table->string('action2_url')->nullable();
    $table->string('action3_url')->nullable();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cei_supports', function (Blueprint $table) {
            //
        });
    }
};
