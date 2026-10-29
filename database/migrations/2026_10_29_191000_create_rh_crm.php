<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rh_access_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        foreach (config('rh.modules') as $module => $definition) {
            Schema::create('rh_'.$module, function (Blueprint $table) use ($module, $definition) {
                $table->id();
                foreach ($definition['fields'] as $key => $field) {
                    if ($field['type'] === 'relation') {
                        $column = $table->foreignId($key);
                        if (!$field['required']) $column->nullable();
                        $column->constrained($field['table'])->restrictOnDelete();
                    } else {
                        $column = match ($field['type']) {
                            'date' => $table->date($key),
                            'datetime-local' => $table->dateTime($key),
                            'textarea' => $table->text($key),
                            'number' => $table->unsignedInteger($key),
                            'decimal' => $table->decimal($key, 14, 2),
                            default => $table->string($key),
                        };
                        if (!$field['required']) $column->nullable();
                    }
                }
                if ($module === 'employees') {
                    $table->unique('registration');
                    $table->unique('user_id');
                }
                if ($module === 'attendance') {
                    $table->unique('schedule_id');
                    $table->unsignedInteger('worked_minutes')->nullable();
                    $table->unsignedInteger('late_minutes')->default(0);
                    $table->unsignedInteger('overtime_minutes')->nullable();
                }
                if ($module === 'balances') {
                    $table->decimal('used', 10, 2)->default(0);
                    $table->unique(['employee_id', 'year', 'type']);
                }
                if ($module === 'applications') $table->foreignId('employee_id')->nullable()->constrained('rh_employees')->restrictOnDelete();
                if ($module === 'payroll') $table->timestamp('transmitted_at')->nullable();
                if ($module === 'leaves') $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                if (isset($definition['fields']['employee_id'])) $table->index('employee_id');
                if (isset($definition['fields']['status'])) $table->index('status');
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse(array_keys(config('rh.modules'))) as $module) Schema::dropIfExists('rh_'.$module);
        Schema::dropIfExists('rh_access_accounts');
    }
};
