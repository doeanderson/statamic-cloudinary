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

class CreateFolderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var AssetFolder
     */
    protected $assetFolder;

    /**
     * @param AssetFolder $assetFolder
     */
    public function __construct(AssetFolder $assetFolder)
    {
        $this->assetFolder = $assetFolder;
    }

    public function handle()
    {
        Cloudinary::admin()->createFolder(
            CloudinaryHelper::getCloudinaryUploadFolder($this->assetFolder->container()) . '/' . $this->assetFolder->path()
        );
    }
}
