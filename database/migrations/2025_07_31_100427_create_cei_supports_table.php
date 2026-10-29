<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cei_supports', function (Blueprint $table) {
            $table->id();
            $table->string('titre')->nullable();
            $table->text('slogan')->nullable();
            $table->string('image_path')->nullable();
            $table->text('introduction')->nullable();
            for ($i = 1; $i <= 4; $i++) {
                $table->text('raison_'.$i)->nullable();
            }
            for ($i = 1; $i <= 3; $i++) {
                $table->string('action'.$i.'_icon')->nullable();
                $table->string('action'.$i.'_title')->nullable();
                $table->text('action'.$i.'_text')->nullable();
            }
            $table->string('social_name')->nullable();
            $table->string('social_url')->nullable();
            $table->string('social_icon')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cei_supports');
    }
};
