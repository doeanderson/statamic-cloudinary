<?php

namespace DoeAnderson\StatamicCloudinary\Jobs;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use DoeAnderson\StatamicCloudinary\Helpers\CloudinaryHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Statamic\Assets\Asset;
use Statamic\Facades\Asset as AssetApi;

class ProcessSavedAssetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Asset
     */
    protected $asset;

    /**
     * @var string|null
     */
    protected $oldPath;

    /**
     * @param Asset $asset
     * @param string|null $oldPath
     */
    public function __construct(Asset $asset, string $oldPath = null)
    {
        $this->asset = $asset;
        $this->oldPath = $oldPath;
    }

    public function handle()
    {
        $currentPublicId = $this->asset->get('cloudinary_public_id');
        if (is_null($currentPublicId)) {
            return;
        }

        if (! is_null($this->oldPath)) {
            $newPublicId = CloudinaryHelper::getPublicId($this->asset);
            Cloudinary::rename(
                $currentPublicId,
                $newPublicId
            );

            $this->asset->set('cloudinary_public_id', $newPublicId);

            // Save asset without emitting AssetSaved event again.
            AssetApi::save($this->asset);
            Cache::forget($this->asset->metaCacheKey());
        }
    }
}
