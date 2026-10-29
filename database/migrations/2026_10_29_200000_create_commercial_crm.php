<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private function modules(): array
    {
        return array_diff_key(config('commercial.modules'), array_flip(['contacts', 'interactions']));
    }
    public function up(): void
    {
        Schema::create('commercial_access_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('employee_id')->unique()->constrained('rh_employees')->restrictOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        foreach ($this->modules() as $module => $definition) {
            Schema::create('com_'.$module, function (Blueprint $table) use ($module, $definition) {
                $table->id();
                foreach ($definition['fields'] as $key => $field) {
                    if ($field['type'] === 'relation') {
                        $column = $table->foreignId($key);
                        if (!$field['required']) $column->nullable();
                        $column->constrained($field['table'])->restrictOnDelete();
                    } else {
                        $column = match ($field['type']) {
                            'textarea' => $table->text($key),
                            'date' => $table->date($key),
                            'datetime-local' => $table->dateTime($key),
                            'number' => $table->unsignedInteger($key),
                            'decimal' => $table->decimal($key, 16, 2),
                            default => $table->string($key),
                        };
                        if (!$field['required']) $column->nullable();
                    }
                }
                if ($module === 'orders') { $table->decimal('amount', 16, 2); $table->unique('quote_id'); }
                if ($module === 'contracts') {
                    $table->string('legal_status')->default('pending');
                    $table->text('legal_notes')->nullable();
                    $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if ($module === 'commissions') {
                    $table->decimal('amount', 16, 2);
                    $table->string('status')->default('proposed');
                    $table->foreignId('payroll_id')->nullable()->constrained('rh_payroll')->restrictOnDelete();
                    $table->foreignId('hr_approved_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->unique(['employee_id', 'order_id']);
                }
                if ($module === 'problems') {
                    $table->string('status')->default('reported')->index();
                    $table->foreignId('cluster_id')->nullable()->constrained('com_clusters')->restrictOnDelete();
                    $table->text('stage_notes')->nullable();
                    $table->timestamp('transmitted_at')->nullable();
                }
                if (in_array($module, ['proposals', 'solutions'])) $table->unique('problem_id');
                if ($module === 'invoices') $table->unique('name');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                if (isset($definition['fields']['employee_id'])) $table->index('employee_id');
            });
        }
        Schema::table('com_prospects', fn (Blueprint $table) => $table->foreignId('converted_client_id')->nullable()->constrained('com_clients')->nullOnDelete());
        Schema::create('com_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('com_invoices')->restrictOnDelete();
            $table->string('reference')->unique();
            $table->date('paid_on');
            $table->decimal('amount', 16, 2);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('com_payments');
        // Clients are referenced by converted prospects, which must be dropped first.
        Schema::table('com_prospects', fn (Blueprint $table) => $table->dropForeign(['converted_client_id']));
        foreach (array_reverse(array_keys($this->modules())) as $module) Schema::dropIfExists('com_'.$module);
        Schema::dropIfExists('commercial_access_accounts');
    }
};
