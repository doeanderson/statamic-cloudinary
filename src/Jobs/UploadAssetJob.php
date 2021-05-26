<?php

namespace DoeAnderson\StatamicCloudinary\Jobs;

use CloudinaryLabs\CloudinaryLaravel\CloudinaryEngine;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use DoeAnderson\StatamicCloudinary\Helpers\CloudinaryHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Statamic\Assets\Asset;

class UploadAssetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Asset
     */
    protected $asset;

    /**
     * @param Asset $asset
     */
    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    public function handle()
    {
        $cloudinaryEngine = Cloudinary::upload(
            $this->asset->resolvedPath(),
            [
                'public_id' => CloudinaryHelper::getPublicId($this->asset),
                'folder' => CloudinaryHelper::getCloudinaryUploadFolder($this->asset->container()),
            ]
        );
        /* @var $cloudinaryEngine CloudinaryEngine */

        $this->asset
            ->set('cloudinary_public_id', $cloudinaryEngine->getResponse()['public_id'])
            ->save();
    }
}
