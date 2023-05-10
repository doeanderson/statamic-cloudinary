<?php

namespace DoeAnderson\StatamicCloudinary\Console\Commands;

use DoeAnderson\StatamicCloudinary\Jobs\UploadAssetJob;
use Illuminate\Console\Command;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\AssetContainer as AssetContainerApi;

class UploadAssets extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:cloudinary:upload-assets
                            {container? : Asset container (ie: "assets")}
                            {assetPath? : Asset id/path within container. (ie: "photo.jpg")}
                            {--a|all : Upload all assets for the given asset container (or all asset containers if none specified).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uploads asset(s) to Cloudinary.';

    /**
     * @var array
     */
    protected $mappedAssetContainers = [];

    /**
     * @var AssetContainer
     */
    protected $container;

    /**
     * @var Asset
     */
    protected $asset;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (! $this->validateInput()) {
            return 1;
        }

        if (! $this->setup()) {
            return 1;
        }

        if ($this->option('all')) {
            $this->uploadAllAssets();
            return 0;
        }

        $this->uploadSingleAsset();

        return 0;
    }

    /**
     * @return bool
     */
    protected function validateInput(): bool
    {
        $container = $this->argument('container');
        $assetPath = $this->argument('assetPath');
        $all = $this->option('all');

        if (! isset($container) && ! isset($assetPath) && ! $all) {
            $this->error('Neither container, assetPath, nor --all are set. Nothing to do.');
            return false;
        }

        foreach (config('statamic.cloudinary.asset_container_mappings', []) as $mapping) {
            $this->mappedAssetContainers[$mapping['asset_container']] = $mapping['asset_container'];
        }

        if (empty($this->mappedAssetContainers)) {
            $this->error('No asset containers configured for Cloudinary.');
            return false;
        }

        if ($container && ! in_array($container, $this->mappedAssetContainers)) {
            $this->error('This asset conatiner is not configured for Cloudinary.');
            return false;
        }

        if (isset($container) && ! isset($assetPath) && ! $all) {
            $this->error('No assetPath set. Either set an assetPath or use the --all option to upload all assets for the container.');
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function setup(): bool
    {
        $container = $this->argument('container');
        $assetPath = $this->argument('assetPath');

        if (isset($container)) {
            $this->container = AssetContainerApi::findByHandle($container);
            if (is_null($this->container)) {
                $this->error('Asset container not found.');
                return false;
            }

            if (isset($assetPath)) {
                $this->asset = $this->container->asset($assetPath);
                if (is_null($this->asset)) {
                    $this->error('Asset not found in container.');
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function uploadAllAssets(): bool
    {
        $assetContainers = collect([]);

        if (isset($this->container)) {
            $assetContainers->put($this->container->handle(), $this->container);
        } else {
            foreach ($this->mappedAssetContainers as $assetContainerHandle) {
                $assetContainer = AssetContainerApi::findByHandle($assetContainerHandle);
                if (is_null($assetContainer)) {
                    $this->error("Could not find asset container handle '{$assetContainerHandle}'");
                    return false;
                }
                $assetContainers->put($assetContainerHandle, $assetContainer);
            }
        }

        $assetContainers->each(function (AssetContainer $assetContainer) {
            $this->info("Uploading assets from container: {$assetContainer->handle()}");
            $assetContainer->assets()->each(function (Asset $asset) {
                $this->output->write("Uploading: {$asset->path()}...");
                dispatch_sync(new UploadAssetJob($asset));
                $this->output->write('Done!');
                $this->newLine();
            });
            $this->newLine();
        });

        return true;
    }

    /**
     * @return bool
     */
    protected function uploadSingleAsset(): bool
    {
        if (! isset($this->container)) {
            return false;
        }

        if (! isset($this->asset)) {
            return false;
        }

        $this->info("Uploading assets from container: {$this->container->handle()}");
        $this->output->write("Uploading: {$this->asset->path()}...");
        dispatch_sync(new UploadAssetJob($this->asset));
        $this->output->write('Done!');
        $this->newLine();

        return true;
    }
}
