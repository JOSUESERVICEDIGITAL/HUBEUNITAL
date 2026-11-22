<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dev_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role');
            $table->string('speciality')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('dev_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('engineering_project_id')->nullable()->constrained('eng_projects')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('eng_clients')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('product_type');
            $table->string('status');
            $table->string('priority')->default('medium');
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->text('repository_url')->nullable();
            $table->text('description')->nullable();
            $table->text('technical_scope')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('dev_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('project_id')->constrained('dev_projects')->restrictOnDelete();
            $table->string('kind');
            $table->string('priority');
            $table->string('status');
            $table->text('acceptance_criteria')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('dev_sprints', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('project_id')->constrained('dev_projects')->restrictOnDelete();
            $table->string('goal');
            $table->string('status');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('review_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('dev_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('project_id')->constrained('dev_projects')->restrictOnDelete();
            $table->foreignId('sprint_id')->nullable()->constrained('dev_sprints')->nullOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kind');
            $table->string('priority');
            $table->string('status');
            $table->date('due_date')->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('dev_repositories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('project_id')->constrained('dev_projects')->restrictOnDelete();
            $table->text('url');
            $table->string('provider');
            $table->string('default_branch')->default('main');
            $table->string('visibility');
            $table->string('status');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('dev_deliverables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('project_id')->constrained('dev_projects')->restrictOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('dev_tasks')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kind');
            $table->string('version');
            $table->string('status');
            $table->text('document_url')->nullable();
            $table->date('due_date')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('dev_deployments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('project_id')->constrained('dev_projects')->restrictOnDelete();
            $table->string('environment');
            $table->string('version');
            $table->string('status');
            $table->dateTime('deployed_at');
            $table->foreignId('deployed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rollback_plan')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('dev_incidents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('project_id')->constrained('dev_projects')->restrictOnDelete();
            $table->string('severity');
            $table->string('status');
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('detected_at');
            $table->dateTime('resolved_at')->nullable();
            $table->text('description');
            $table->text('resolution')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('dev_maintenance', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('project_id')->constrained('dev_projects')->restrictOnDelete();
            $table->string('kind');
            $table->string('status');
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['dev_maintenance','dev_incidents','dev_deployments','dev_deliverables','dev_repositories','dev_tasks','dev_sprints','dev_requirements','dev_projects','dev_team'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
