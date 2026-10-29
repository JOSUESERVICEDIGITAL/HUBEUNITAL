<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['contacts', 'interactions'] as $module) {
            Schema::create('com_'.$module, function (Blueprint $table) use ($module) {
                $table->id();
                $table->string('name');
                $table->foreignId('employee_id')->constrained('rh_employees')->restrictOnDelete();
                $table->foreignId('client_id')->constrained('com_clients')->restrictOnDelete();
                if ($module === 'contacts') {
                    $table->string('position')->nullable(); $table->string('email')->nullable(); $table->string('phone')->nullable(); $table->text('notes')->nullable();
                } else {
                    $table->date('date'); $table->string('type'); $table->text('summary');
                }
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['client_id', 'employee_id']);
            });
        }
        foreach (array_keys(config('commercial.modules')) as $module) {
            Schema::table('com_'.$module, fn (Blueprint $table) => $table->timestamp('archived_at')->nullable()->index());
        }
        Schema::create('com_quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained('com_quotes')->cascadeOnDelete();
            $table->string('description', 500);
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price', 16, 2);
            $table->decimal('amount', 16, 2);
            $table->timestamps();
        });
        Schema::create('com_documents', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->unsignedBigInteger('record_id');
            $table->string('name');
            $table->string('path');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['module', 'record_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('com_documents'); Schema::dropIfExists('com_quote_items');
        foreach (array_keys(config('commercial.modules')) as $module) Schema::table('com_'.$module, fn (Blueprint $table) => $table->dropColumn('archived_at'));
        Schema::dropIfExists('com_interactions'); Schema::dropIfExists('com_contacts');
    }
};
