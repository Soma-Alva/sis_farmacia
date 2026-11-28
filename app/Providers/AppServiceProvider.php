<?php

namespace App\Providers;

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
        // @dinero($valor): monto con hasta 4 decimales, sin ceros sobrantes
        \Illuminate\Support\Facades\Blade::directive('dinero', function ($expresion) {
            return "<?php echo \\App\\Support\\Dinero::fmt({$expresion}); ?>";
        });
    }
}
