<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Category;
use App\Models\CeiSupport;
use App\Models\Partenaire;
use App\Models\Settings;
use App\Models\SocialMedia;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;
use Spatie\Tags\Tag;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(\App\Services\RhAuthorization::class);
        $this->app->scoped(\App\Services\CommercialAuthorization::class);
        // Resolve shared data only when a view is rendered, once per request.
        $this->app->scoped('view.global-data', function () {
            $settings = Settings::first();

            return [
                'global_social' => SocialMedia::orderByDesc('id')->get(),
                'global_category' => Category::where('isActive', 1)->latest()->get(),
                'global_setting' => $settings,
                'global_settings' => $settings,
                'global_recent_articles' => Article::where('isActive', 1)->latest()->take(5)->get(),
                'global_tags' => Tag::whereNotNull('id')->orderByDesc('id')->take(15)->get(),
                'global_famous_articles' => Article::where('isActive', 1)->latest()->orderByDesc('views')->take(3)->get(),
                'global_partners' => Partenaire::orderByDesc('id')->get(),
                'global_cei_support' => CeiSupport::latest()->first(),
            ];
        });
    }

    public function boot(): void
    {
        view()->composer('back.partials.sidebar', function (View $view) {
            $view->with('sidebarChambers', \App\Models\Chamber::orderBy('id')->get());
        });
        view()->composer('*', function (View $view) {
            $view->with($this->app->make('view.global-data'));
        });
    }
}
