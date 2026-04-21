<?php

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Services\Payment\MockPaymentGateway;
use App\Services\Payment\PortonePaymentGateway;
use App\Services\ReviewService;
use App\Services\SearchService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGatewayInterface::class, function () {
            return match (strtolower(config('services.payment_gateway', 'mock'))) {
                'portone' => new PortonePaymentGateway(),
                default   => new MockPaymentGateway(),
            };
        });

        $this->app->singleton(SearchService::class, fn () => new SearchService());
        $this->app->singleton(ReviewService::class, fn () => new ReviewService());
    }

    public function boot(): void
    {
        //
    }
}
