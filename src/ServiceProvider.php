<?php

namespace DoeAnderson\StatamicCloudinary;

use DoeAnderson\StatamicCloudinary\Actions\UploadToCloudinaryAction;
use DoeAnderson\StatamicCloudinary\Subscriber;
use Statamic\Console\Commands\Install;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    /**
     * @var string[]
     */
    protected $routes = [
        'cp' => __DIR__ . '/../routes/cp.php',
    ];

    protected $actions = [
        UploadToCloudinaryAction::class,
    ];

    /**
     * @var string[]
     */
    protected $subscribe = [
        Subscriber::class,
    ];

    public function boot(): void
    {
        parent::boot();

        $this
            ->bootAddonConfig()
            ->bootAddonViews()
            ->bootPermissions()
            ->bootAddonNav()
            ->bootPostInstall();
    }

    protected function bootAddonNav(): self
    {
        Nav::extend(function (\Statamic\CP\Navigation\Nav $nav) {

            $nav
                ->content('Cloudinary')
                ->section('Addon Settings')
                ->route('cloudinary.config.edit')
                ->can('configure cloudinary')
                ->icon('settings-horizontal');
        });

        return $this;
    }

    protected function bootAddonViews(): self
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views/cp', 'cloudinary');

        return $this;
    }

    protected function bootPermissions(): self
    {
        Permission::group('cloudinary', 'Cloudinary', function () {
            Permission::register('configure cloudinary')
                ->label('Configure Settings');
        });

        return $this;
    }

    protected function bootAddonConfig(): self
    {
        $this->publishes(
            [
                __DIR__ . '/../config/cloudinary.php' => config_path('statamic/cloudinary.php'),
            ],
            'statamic-cloudinary-config'
        );

        return $this;
    }

    protected function bootPostInstall(): self
    {
        Statamic::afterInstalled(function (Install $command) {
            $command->call('vendor:publish', [
                '--tag' => 'statamic-cloudinary-config',
            ]);
        });

        return $this;
    }
}
