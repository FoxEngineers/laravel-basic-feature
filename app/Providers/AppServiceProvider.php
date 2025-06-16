<?php

namespace App\Providers;

use App\Constants\Constant;
use App\Models\PassportToken;
use Carbon\CarbonInterval;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

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
        Passport::enablePasswordGrant();
        Passport::useTokenModel(PassportToken::class);
        Passport::personalAccessTokensExpireIn(CarbonInterval::minutes(Constant::TOKENS_EXPIRE_IN));
    }
}
