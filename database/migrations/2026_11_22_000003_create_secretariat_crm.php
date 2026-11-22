<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sec_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role');
            $table->string('speciality')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('sec_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('type');
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('sec_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('client_id')->constrained('sec_clients')->restrictOnDelete();
            $table->string('service');
            $table->string('title');
            $table->text('specifications')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->string('priority')->default('normal');
            $table->string('status');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('sec_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('sec_orders')->cascadeOnDelete();
            $table->string('name');
            $table->string('workstation');
            $table->string('status');
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->text('file_url')->nullable();
            $table->text('quality_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('sec_quotes', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('client_id')->constrained('sec_clients')->restrictOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('sec_orders')->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->date('valid_until')->nullable();
            $table->string('status');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('sec_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('client_id')->constrained('sec_clients')->restrictOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('sec_orders')->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->date('issued_on');
            $table->date('due_on')->nullable();
            $table->string('status');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('sec_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('sec_invoices')->restrictOnDelete();
            $table->string('name');
            $table->decimal('amount', 14, 2);
            $table->date('payment_date');
            $table->string('method');
            $table->string('reference')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('sec_stock', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->string('unit');
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('minimum_quantity', 12, 2)->default(0);
            $table->decimal('unit_cost', 14, 2)->default(0);
            $table->string('location')->nullable();
            $table->string('status')->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('sec_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('sec_stock')->cascadeOnDelete();
            $table->string('type');
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_cost', 14, 2)->default(0);
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['sec_stock_movements','sec_stock','sec_payments','sec_invoices','sec_quotes','sec_jobs','sec_orders','sec_clients','sec_team'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
