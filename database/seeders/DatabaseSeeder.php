<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ChamberCategorySeeder::class);

        // User::factory(10)->create();

        User::firstOrCreate(['email' => 'test@example.com'], [
            'name' => 'Test User',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        \App\Models\Settings::firstOrCreate([], [
            'website_name' => 'CEI',
            'about' => 'Bienvenue sur le site du CEI.',
        ]);
        \App\Models\CeiSupport::firstOrCreate([], [
            'titre' => 'Soutenir le CEI',
        ]);
        \App\Models\DonationSetting::firstOrCreate([], [
            'titre' => 'Faire un don',
        ]);
        \App\Models\VolunteerSetting::firstOrCreate([], [
            'message_confirmation' => 'Merci pour votre demande. Nous vous contacterons prochainement.',
        ]);

        $this->call([ChamberSeeder::class, EngineeringArticleSeeder::class, MarketingSeeder::class, StudioSeeder::class, GraphismeSeeder::class, JuridiqueArticleSeeder::class, RhSeeder::class, CommercialSeeder::class, MissingChamberArticlesSeeder::class]);
    }
}
