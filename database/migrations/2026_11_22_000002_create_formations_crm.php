<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('frm_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role');
            $table->string('speciality')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('frm_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('format');
            $table->string('level')->nullable();
            $table->string('status');
            $table->unsignedInteger('duration_hours')->default(0);
            $table->decimal('price', 14, 2)->default(0);
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();
            $table->text('prerequisites')->nullable();
            $table->foreignId('engineering_project_id')->nullable()->constrained('eng_projects')->nullOnDelete();
            $table->foreignId('development_project_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('frm_cohorts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('program_id')->constrained('frm_programs')->restrictOnDelete();
            $table->string('status');
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->string('location')->nullable();
            $table->unsignedInteger('capacity')->default(20);
            $table->foreignId('trainer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('frm_learners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('birth_date')->nullable();
            $table->string('city')->nullable();
            $table->string('education_level')->nullable();
            $table->string('status');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('frm_applications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('learner_id')->nullable()->constrained('frm_learners')->nullOnDelete();
            $table->foreignId('program_id')->constrained('frm_programs')->restrictOnDelete();
            $table->foreignId('cohort_id')->nullable()->constrained('frm_cohorts')->nullOnDelete();
            $table->string('email');
            $table->string('phone');
            $table->string('source')->nullable();
            $table->string('status');
            $table->text('motivation')->nullable();
            $table->text('decision_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('frm_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learner_id')->constrained('frm_learners')->restrictOnDelete();
            $table->foreignId('cohort_id')->constrained('frm_cohorts')->restrictOnDelete();
            $table->string('status');
            $table->date('enrolled_on');
            $table->decimal('fees_amount', 14, 2)->default(0);
            $table->decimal('fees_paid', 14, 2)->default(0);
            $table->decimal('attendance_rate', 5, 2)->default(0);
            $table->decimal('average_score', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['learner_id', 'cohort_id']);
        });
        Schema::create('frm_content', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('program_id')->constrained('frm_programs')->restrictOnDelete();
            $table->string('kind');
            $table->unsignedInteger('order')->default(1);
            $table->string('status');
            $table->unsignedInteger('duration_minutes')->default(0);
            $table->text('document_url')->nullable();
            $table->text('objectives')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('frm_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('cohort_id')->constrained('frm_cohorts')->restrictOnDelete();
            $table->foreignId('content_id')->nullable()->constrained('frm_content')->nullOnDelete();
            $table->foreignId('trainer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('location');
            $table->string('status');
            $table->text('materials')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('frm_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('frm_sessions')->restrictOnDelete();
            $table->foreignId('learner_id')->constrained('frm_learners')->restrictOnDelete();
            $table->string('status');
            $table->dateTime('checked_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['session_id', 'learner_id']);
        });
        Schema::create('frm_evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('cohort_id')->constrained('frm_cohorts')->restrictOnDelete();
            $table->foreignId('learner_id')->constrained('frm_learners')->restrictOnDelete();
            $table->string('type');
            $table->decimal('score', 5, 2);
            $table->decimal('max_score', 5, 2)->default(100);
            $table->date('evaluated_on');
            $table->text('feedback')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('frm_certificates', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('learner_id')->constrained('frm_learners')->restrictOnDelete();
            $table->foreignId('cohort_id')->constrained('frm_cohorts')->restrictOnDelete();
            $table->string('status');
            $table->date('issued_on')->nullable();
            $table->text('document_url')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['learner_id', 'cohort_id']);
        });
        Schema::create('frm_talents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learner_id')->constrained('frm_learners')->restrictOnDelete();
            $table->string('speciality');
            $table->string('availability');
            $table->string('status');
            $table->decimal('readiness_score', 5, 2)->default(0);
            $table->text('portfolio_url')->nullable();
            $table->text('skills')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['frm_talents','frm_certificates','frm_evaluations','frm_attendance','frm_sessions','frm_content','frm_enrollments','frm_applications','frm_learners','frm_cohorts','frm_programs','frm_team'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
