<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (config('juridique.modules') as $module => $definition) {
            Schema::create('jur_'.$module, function (Blueprint $table) use ($module, $definition) {
                $table->id();
                foreach ($definition['fields'] as $key => $field) {
                    if ($field['type'] === 'relation') {
                        $column = $table->foreignId($key);
                        if (!$field['required']) $column->nullable();
                        $column->constrained($field['table'])->restrictOnDelete();
                    } else {
                        $column = match ($field['type']) {
                            'date' => $table->date($key),
                            'textarea' => $table->text($key),
                            default => $table->string($key),
                        };
                        if (!$field['required']) $column->nullable();
                    }
                }
                if ($module === 'team') $table->unique('user_id');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse(array_keys(config('juridique.modules'))) as $module) Schema::dropIfExists('jur_'.$module);
    }
};
