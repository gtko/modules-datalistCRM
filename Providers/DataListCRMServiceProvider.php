<?php

namespace Modules\DataListCRM\Providers;

use Config;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class DataListCRMServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'DataListCRM';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'datalistcrm';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Blade::componentNamespace('Modules\DataListCRM\View\Components', $this->moduleNameLower);
        $this->registerViews();
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }

}
