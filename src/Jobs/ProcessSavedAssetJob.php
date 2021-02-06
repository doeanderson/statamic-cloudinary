<?php

namespace DoeAnderson\StatamicCloudinary\Jobs;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use DoeAnderson\StatamicCloudinary\Helpers\CloudinaryHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetFolder;

class ProcessSavedAssetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Asset
     */
    protected $asset;

    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    public function handle()
    {
        $currentPublicId = $this->asset->get('cloudinary_public_id');
        if (is_null($currentPublicId)) {
            return;
        }

        if (CloudinaryHelper::isAssetRenamed($this->asset)) {
            $newPublicId = CloudinaryHelper::getPublicId($this->asset);
            Cloudinary::rename(
                $currentPublicId,
                $newPublicId
            );

            $this->asset->set('cloudinary_public_id', $newPublicId);
            $this->asset->save();
        }
    }
}
