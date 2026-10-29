<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chamber_audits', function (Blueprint $table) {
            $table->string('chamber_slug')->default('ingenierie')->index();
        });
        foreach (DB::table('chambers')->get() as $chamber) {
            DB::table('chamber_audits')->where('module', 'chambers')->where('record_id', $chamber->id)->update(['chamber_slug' => $chamber->slug]);
        }
        Schema::create('mkt_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('status')->index();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('mkt_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('client_id')->constrained('mkt_clients')->restrictOnDelete();
            $table->string('position')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('mkt_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->string('url')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('status')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('mkt_audiences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('client_id')->constrained('mkt_clients')->restrictOnDelete();
            $table->unsignedInteger('estimated_size')->nullable();
            $table->string('status')->index();
            $table->text('criteria')->nullable();
            $table->text('consent_basis');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('mkt_strategies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('client_id')->constrained('mkt_clients')->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('version');
            $table->string('status')->index();
            $table->text('positioning');
            $table->text('objectives');
            $table->text('competitors')->nullable();
            $table->string('guidelines_url')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('mkt_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('client_id')->constrained('mkt_clients')->restrictOnDelete();
            $table->foreignId('strategy_id')->nullable()->constrained('mkt_strategies')->restrictOnDelete();
            $table->foreignId('audience_id')->nullable()->constrained('mkt_audiences')->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('objective');
            $table->string('status')->index();
            $table->date('start_date');
            $table->date('due_date')->index();
            $table->decimal('budget', 14, 2);
            $table->unsignedInteger('target_leads')->nullable();
            $table->decimal('target_revenue', 14, 2)->nullable();
            $table->text('brief');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('mkt_leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('client_id')->nullable()->constrained('mkt_clients')->restrictOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('mkt_campaigns')->restrictOnDelete();
            $table->foreignId('channel_id')->nullable()->constrained('mkt_channels')->restrictOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('status')->index();
            $table->decimal('estimated_value', 14, 2)->nullable();
            $table->date('due_date')->nullable()->index();
            $table->string('contact_permission');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('mkt_contents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('campaign_id')->constrained('mkt_campaigns')->restrictOnDelete();
            $table->foreignId('channel_id')->constrained('mkt_channels')->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('format');
            $table->string('status')->index();
            $table->string('version');
            $table->date('due_date')->nullable()->index();
            $table->date('published_on')->nullable();
            $table->string('content_url')->nullable();
            $table->text('copy');
            $table->text('feedback')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('mkt_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('campaign_id')->constrained('mkt_campaigns')->restrictOnDelete();
            $table->foreignId('content_id')->nullable()->constrained('mkt_contents')->restrictOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('status')->index();
            $table->string('priority');
            $table->date('due_date')->nullable()->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('mkt_results', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('campaign_id')->constrained('mkt_campaigns')->restrictOnDelete();
            $table->foreignId('channel_id')->constrained('mkt_channels')->restrictOnDelete();
            $table->date('recorded_on')->index();
            $table->unsignedInteger('impressions');
            $table->unsignedInteger('clicks');
            $table->unsignedInteger('leads');
            $table->unsignedInteger('conversions');
            $table->decimal('spend', 14, 2);
            $table->decimal('revenue', 14, 2);
            $table->string('source_url')->nullable();
            $table->text('notes')->nullable();
            $table->unique(['campaign_id', 'channel_id', 'recorded_on'], 'mkt_results_daily_unique');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('mkt_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('campaign_id')->constrained('mkt_campaigns')->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->date('spent_on');
            $table->string('category');
            $table->string('receipt_url')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('mkt_activities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('client_id')->nullable()->constrained('mkt_clients')->restrictOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('mkt_campaigns')->restrictOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('mkt_leads')->restrictOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('mkt_contacts')->restrictOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('type');
            $table->string('status')->index();
            $table->date('due_date')->nullable()->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('mkt_assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('client_id')->constrained('mkt_clients')->restrictOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('mkt_campaigns')->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('type');
            $table->string('version');
            $table->string('file_url');
            $table->text('usage_rights');
            $table->string('status')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('mkt_team', function (Blueprint $table) {
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
        Schema::dropIfExists('mkt_team');
        Schema::dropIfExists('mkt_assets');
        Schema::dropIfExists('mkt_activities');
        Schema::dropIfExists('mkt_expenses');
        Schema::dropIfExists('mkt_results');
        Schema::dropIfExists('mkt_tasks');
        Schema::dropIfExists('mkt_contents');
        Schema::dropIfExists('mkt_leads');
        Schema::dropIfExists('mkt_campaigns');
        Schema::dropIfExists('mkt_strategies');
        Schema::dropIfExists('mkt_audiences');
        Schema::dropIfExists('mkt_channels');
        Schema::dropIfExists('mkt_contacts');
        Schema::dropIfExists('mkt_clients');
        Schema::table('chamber_audits', function (Blueprint $table) {
            $table->dropColumn('chamber_slug');
        });
    }
};
