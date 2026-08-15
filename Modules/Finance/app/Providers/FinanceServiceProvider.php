<?php

declare(strict_types=1);

namespace Modules\Finance\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class FinanceServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Finance';

    protected string $moduleNameLower = 'finance';

    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
        $this->registerRoutes();
    }

    public function register(): void
    {
        $this->app->register(RepositoryServiceProvider::class);
    }

    protected function registerRoutes(): void
    {
        $webRoutePath = module_path($this->moduleName, 'routes/web.php');
        if (file_exists($webRoutePath)) {
            Route::middleware('web')
                ->group($webRoutePath);
        }

        $apiRoutePath = module_path($this->moduleName, 'routes/api.php');
        if (file_exists($apiRoutePath)) {
            Route::middleware('api')
                ->prefix('api/finance')
                ->group($apiRoutePath);
        }
    }

    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom(array_merge($this->getViewsPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    protected function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'lang'));
        }
    }

    protected function registerCommands(): void {}

    protected function registerCommandSchedules(): void {}

    private function getViewsPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->moduleNameLower)) {
                $paths[] = $path.'/modules/'.$this->moduleNameLower;
            }
        }

        return $paths;
    }
}
