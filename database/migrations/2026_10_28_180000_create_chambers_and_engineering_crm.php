<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chambers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->boolean('is_open')->default(false);
            $table->timestamps();
        });
        Schema::create('eng_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('status')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('eng_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('client_id')->constrained('eng_clients')->restrictOnDelete();
            $table->string('position')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('eng_opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('client_id')->constrained('eng_clients')->restrictOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('eng_contacts')->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->unsignedInteger('probability');
            $table->string('status')->index();
            $table->date('due_date')->nullable()->index();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('eng_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('client_id')->constrained('eng_clients')->restrictOnDelete();
            $table->foreignId('opportunity_id')->nullable()->constrained('eng_opportunities')->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('status')->index();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable()->index();
            $table->decimal('budget', 14, 2);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('eng_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('project_id')->constrained('eng_projects')->restrictOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('status')->index();
            $table->string('priority');
            $table->date('due_date')->nullable()->index();
            $table->unsignedInteger('estimated_hours')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('eng_deliverables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('project_id')->constrained('eng_projects')->restrictOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('eng_tasks')->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('status')->index();
            $table->string('version');
            $table->date('due_date')->nullable()->index();
            $table->string('document_url')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('eng_quotes', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('client_id')->constrained('eng_clients')->restrictOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('eng_projects')->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('status')->index();
            $table->date('due_date')->nullable()->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('eng_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('client_id')->constrained('eng_clients')->restrictOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('eng_projects')->restrictOnDelete();
            $table->foreignId('quote_id')->nullable()->constrained('eng_quotes')->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('status')->index();
            $table->date('due_date')->nullable()->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('eng_payments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('invoice_id')->constrained('eng_invoices')->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->date('payment_date');
            $table->string('method');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('eng_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('project_id')->constrained('eng_projects')->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->date('spent_on');
            $table->string('category');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('eng_activities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('client_id')->constrained('eng_clients')->restrictOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('eng_projects')->restrictOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('eng_contacts')->restrictOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('type');
            $table->string('status')->index();
            $table->date('due_date')->nullable()->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('eng_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete()->unique();
            $table->string('role');
            $table->string('status')->index();
            $table->string('specialty')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('chamber_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('module');
            $table->unsignedBigInteger('record_id')->nullable();
            $table->string('action');
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chamber_audits');
        Schema::dropIfExists('eng_team');
        Schema::dropIfExists('eng_activities');
        Schema::dropIfExists('eng_expenses');
        Schema::dropIfExists('eng_payments');
        Schema::dropIfExists('eng_invoices');
        Schema::dropIfExists('eng_quotes');
        Schema::dropIfExists('eng_deliverables');
        Schema::dropIfExists('eng_tasks');
        Schema::dropIfExists('eng_projects');
        Schema::dropIfExists('eng_opportunities');
        Schema::dropIfExists('eng_contacts');
        Schema::dropIfExists('eng_clients');
        Schema::dropIfExists('chambers');
    }
};
