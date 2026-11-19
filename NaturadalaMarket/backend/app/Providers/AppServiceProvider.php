<?php

namespace App\Providers;

use App\Mail\NewProductAnnouncement;
use App\Models\Product;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Product::saved(function (Product $product): void {
            if (! $product->active || $product->announced_at !== null) {
                return;
            }

            $product->forceFill(['announced_at' => now()])->saveQuietly();

            Subscriber::query()->active()->each(
                fn (Subscriber $subscriber) => Mail::to($subscriber->email)->queue(new NewProductAnnouncement($product, $subscriber))
            );
        });
    }
}
