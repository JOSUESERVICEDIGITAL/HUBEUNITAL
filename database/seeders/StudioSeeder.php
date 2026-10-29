<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StudioSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(StudioArticleSeeder::class);
    }
}
