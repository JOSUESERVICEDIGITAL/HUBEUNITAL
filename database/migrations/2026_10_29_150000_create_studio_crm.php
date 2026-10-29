<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('std_clients', function (Blueprint $table) {
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
        Schema::create('std_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('client_id')->constrained('std_clients')->restrictOnDelete();
            $table->string('position')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('std_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('client_id')->constrained('std_clients')->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('format');
            $table->string('status')->index();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable()->index();
            $table->decimal('budget', 14, 2);
            $table->text('description');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('std_briefs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('project_id')->constrained('std_projects')->restrictOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('std_contacts')->restrictOnDelete();
            $table->string('version');
            $table->string('status')->index();
            $table->text('objectives');
            $table->text('specifications');
            $table->text('usage_rights');
            $table->string('script_url')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('std_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->text('address');
            $table->string('status')->index();
            $table->string('contact')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('std_equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('inventory_code')->unique();
            $table->string('serial_number')->nullable();
            $table->string('type');
            $table->string('status')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('std_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('project_id')->constrained('std_projects')->restrictOnDelete();
            $table->foreignId('location_id')->constrained('std_locations')->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('type');
            $table->string('status')->index();
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at')->index();
            $table->string('call_sheet_url')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('std_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('session_id')->constrained('std_sessions')->restrictOnDelete();
            $table->foreignId('equipment_id')->constrained('std_equipment')->restrictOnDelete();
            $table->string('status')->index();
            $table->text('notes')->nullable();
            $table->unique(['session_id', 'equipment_id'], 'std_reservations_unique');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('std_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('session_id')->constrained('std_sessions')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('role');
            $table->string('status')->index();
            $table->text('notes')->nullable();
            $table->unique(['session_id', 'user_id'], 'std_assignments_unique');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('std_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('project_id')->constrained('std_projects')->restrictOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('status')->index();
            $table->string('priority');
            $table->date('due_date')->nullable()->index();
            $table->unsignedInteger('estimated_hours')->nullable();
            $table->text('description')->nullable();
            $table->string('task_type');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('std_assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('project_id')->constrained('std_projects')->restrictOnDelete();
            $table->foreignId('session_id')->nullable()->constrained('std_sessions')->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('type');
            $table->string('version');
            $table->string('file_url');
            $table->string('backup_url')->nullable();
            $table->text('usage_rights');
            $table->string('status')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('std_deliverables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('project_id')->constrained('std_projects')->restrictOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('std_tasks')->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('status')->index();
            $table->string('version');
            $table->date('due_date')->nullable()->index();
            $table->string('document_url')->nullable();
            $table->text('description')->nullable();
            $table->string('format');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('std_quotes', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('client_id')->constrained('std_clients')->restrictOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('std_projects')->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('status')->index();
            $table->date('due_date')->nullable()->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('std_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('client_id')->constrained('std_clients')->restrictOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('std_projects')->restrictOnDelete();
            $table->foreignId('quote_id')->nullable()->constrained('std_quotes')->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('status')->index();
            $table->date('due_date')->nullable()->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('std_payments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('invoice_id')->constrained('std_invoices')->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->date('payment_date');
            $table->string('method');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('std_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('project_id')->constrained('std_projects')->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->date('spent_on');
            $table->string('category');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('std_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete()->unique();
            $table->string('role');
            $table->string('status')->index();
            $table->string('specialty')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('std_team');
        Schema::dropIfExists('std_expenses');
        Schema::dropIfExists('std_payments');
        Schema::dropIfExists('std_invoices');
        Schema::dropIfExists('std_quotes');
        Schema::dropIfExists('std_deliverables');
        Schema::dropIfExists('std_assets');
        Schema::dropIfExists('std_tasks');
        Schema::dropIfExists('std_assignments');
        Schema::dropIfExists('std_reservations');
        Schema::dropIfExists('std_sessions');
        Schema::dropIfExists('std_equipment');
        Schema::dropIfExists('std_locations');
        Schema::dropIfExists('std_briefs');
        Schema::dropIfExists('std_projects');
        Schema::dropIfExists('std_contacts');
        Schema::dropIfExists('std_clients');
    }
};
