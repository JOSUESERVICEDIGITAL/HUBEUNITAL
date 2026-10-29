<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gfx_clients', function (Blueprint $table) {
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
        Schema::create('gfx_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('client_id')->constrained('gfx_clients')->restrictOnDelete();
            $table->string('position')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('gfx_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('client_id')->constrained('gfx_clients')->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('kind');
            $table->string('status')->index();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable()->index();
            $table->decimal('budget',14,2);
            $table->text('description');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('gfx_briefs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('project_id')->constrained('gfx_projects')->restrictOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('gfx_contacts')->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('version');
            $table->string('status')->index();
            $table->text('objectives');
            $table->text('audience');
            $table->text('constraints');
            $table->string('references_url')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('gfx_identities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('client_id')->constrained('gfx_clients')->restrictOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('gfx_projects')->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('version');
            $table->string('status')->index();
            $table->text('palette');
            $table->text('typography');
            $table->text('rules');
            $table->string('guidelines_url')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('gfx_assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('client_id')->constrained('gfx_clients')->restrictOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('gfx_projects')->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('type');
            $table->string('version');
            $table->string('source_url');
            $table->string('license_url')->nullable();
            $table->text('usage_rights');
            $table->string('status')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('gfx_designs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('project_id')->constrained('gfx_projects')->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('kind');
            $table->string('status')->index();
            $table->string('dimensions')->nullable();
            $table->string('color_mode');
            $table->date('due_date')->nullable()->index();
            $table->text('description');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('gfx_revisions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('design_id')->constrained('gfx_designs')->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('version');
            $table->string('status')->index();
            $table->string('source_url');
            $table->string('preview_url')->nullable();
            $table->text('change_notes');
            $table->timestamp('approved_at')->nullable();
            $table->unique(['design_id', 'version'], 'gfx_revisions_unique');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('gfx_feedback', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('design_id')->constrained('gfx_designs')->restrictOnDelete();
            $table->foreignId('revision_id')->nullable()->constrained('gfx_revisions')->restrictOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('gfx_contacts')->restrictOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('type');
            $table->string('status')->index();
            $table->string('position')->nullable();
            $table->date('due_date')->nullable()->index();
            $table->text('comment');
            $table->text('reply')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('gfx_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('project_id')->constrained('gfx_projects')->restrictOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('status')->index();
            $table->string('priority');
            $table->date('due_date')->nullable()->index();
            $table->unsignedInteger('estimated_hours')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('gfx_deliverables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('project_id')->constrained('gfx_projects')->restrictOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('gfx_tasks')->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('status')->index();
            $table->string('version');
            $table->date('due_date')->nullable()->index();
            $table->string('document_url')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('revision_id')->constrained('gfx_revisions')->restrictOnDelete();
            $table->string('format');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('gfx_quotes', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('client_id')->constrained('gfx_clients')->restrictOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('gfx_projects')->restrictOnDelete();
            $table->decimal('amount',14,2);
            $table->string('status')->index();
            $table->date('due_date')->nullable()->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('gfx_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('client_id')->constrained('gfx_clients')->restrictOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('gfx_projects')->restrictOnDelete();
            $table->foreignId('quote_id')->nullable()->constrained('gfx_quotes')->restrictOnDelete();
            $table->decimal('amount',14,2);
            $table->string('status')->index();
            $table->date('due_date')->nullable()->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('gfx_payments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('invoice_id')->constrained('gfx_invoices')->restrictOnDelete();
            $table->decimal('amount',14,2);
            $table->date('payment_date');
            $table->string('method');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('gfx_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('project_id')->constrained('gfx_projects')->restrictOnDelete();
            $table->decimal('amount',14,2);
            $table->date('spent_on');
            $table->string('category');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('gfx_team', function (Blueprint $table) {
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
        Schema::dropIfExists('gfx_team');
        Schema::dropIfExists('gfx_expenses');
        Schema::dropIfExists('gfx_payments');
        Schema::dropIfExists('gfx_invoices');
        Schema::dropIfExists('gfx_quotes');
        Schema::dropIfExists('gfx_deliverables');
        Schema::dropIfExists('gfx_tasks');
        Schema::dropIfExists('gfx_feedback');
        Schema::dropIfExists('gfx_revisions');
        Schema::dropIfExists('gfx_designs');
        Schema::dropIfExists('gfx_assets');
        Schema::dropIfExists('gfx_identities');
        Schema::dropIfExists('gfx_briefs');
        Schema::dropIfExists('gfx_projects');
        Schema::dropIfExists('gfx_contacts');
        Schema::dropIfExists('gfx_clients');
    }
};
