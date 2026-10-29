<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Chamber;
use Illuminate\Database\Seeder;

class ChamberSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ChamberCategorySeeder::class);
        foreach (['ingenierie', 'marketing', 'studio', 'graphisme', 'juridique', 'rh', 'innovation', 'developpement', 'formation', 'finance', 'cybersecurite', 'strategie', 'commerciale'] as $slug) {
            $category = Category::withTrashed()->where('slug', $slug)->firstOrFail();
            Chamber::firstOrCreate(['slug' => $slug], [
                'name' => $category->name,
                'category_id' => $category->id,
                'is_open' => $slug === 'ingenierie',
            ]);
        }
    }
}
