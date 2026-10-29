<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class GraphismeSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(GraphismeArticleSeeder::class);
    }
}
