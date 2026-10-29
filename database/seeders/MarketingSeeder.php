<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarketingSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ChamberSeeder::class);
        foreach (['Facebook' => 'social', 'Instagram' => 'social', 'LinkedIn' => 'social', 'Recherche web' => 'search', 'Email' => 'email', 'Site internet' => 'website', 'Événements' => 'event'] as $name => $type) {
            if (! DB::table('mkt_channels')->where('name', $name)->exists()) {
                DB::table('mkt_channels')->insert([
                    'name' => $name, 'type' => $type, 'status' => 'active',
                    'notes' => 'Canal de suivi. Aucun compte externe connecté ; renseignez la page ou le compte utilisé.',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
        $this->call(MarketingArticleSeeder::class);
    }
}
