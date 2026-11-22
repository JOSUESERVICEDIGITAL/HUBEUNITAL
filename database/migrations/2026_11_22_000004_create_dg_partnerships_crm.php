<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('categories')->where('slug', 'strategie')->update([
            'name' => 'DG et Partenariats',
            'description' => 'Pilotage stratégique d’EUNITAL, coordination des chambres, relations institutionnelles et développement des partenariats.',
        ]);
        DB::table('chambers')->where('slug', 'strategie')->update(['name' => 'DG et Partenariats']);

        Schema::create('dg_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role');
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('dg_leadership', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('title');
            $table->string('photo_path');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('mandate_starts_on')->nullable();
            $table->date('mandate_ends_on')->nullable();
            $table->string('status')->default('active');
            $table->text('biography')->nullable();
            $table->text('vision')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('dg_partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo_path');
            $table->string('organization_type');
            $table->string('sector')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_title')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('address')->nullable();
            $table->string('country')->nullable();
            $table->string('status')->default('prospect');
            $table->date('relationship_started_on')->nullable();
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('dg_agreements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('partner_id')->constrained('dg_partners')->restrictOnDelete();
            $table->string('reference')->nullable();
            $table->string('type');
            $table->date('signed_on')->nullable();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->string('status');
            $table->text('objectives')->nullable();
            $table->text('deliverables')->nullable();
            $table->text('document_url')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('dg_meetings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('partner_id')->nullable()->constrained('dg_partners')->nullOnDelete();
            $table->dateTime('starts_at');
            $table->string('location')->nullable();
            $table->string('status');
            $table->text('participants')->nullable();
            $table->text('agenda')->nullable();
            $table->text('minutes')->nullable();
            $table->text('next_steps')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('dg_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('objective');
            $table->string('owner')->nullable();
            $table->date('starts_on')->nullable();
            $table->date('due_on')->nullable();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->string('priority');
            $table->string('status');
            $table->text('success_indicators')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['dg_priorities', 'dg_meetings', 'dg_agreements', 'dg_partners', 'dg_leadership', 'dg_team'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
