<?php

namespace DoeAnderson\StatamicCloudinary\Jobs;

use Cloudinary\Api\Exception\ApiError;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use DoeAnderson\StatamicCloudinary\Helpers\CloudinaryHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Statamic\Assets\AssetFolder;

class DeleteFolderJob implements ShouldQueue
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
        $baseFolder = CloudinaryHelper::getCloudinaryUploadFolder($this->assetFolder->container());
        if (! empty($baseFolder)) {
            $baseFolder .= '/';
        }
        $baseFolder .= $this->assetFolder->path();

        // All subfolders must be deleted first.
        try {
            CloudinaryHelper::getSubFolders($baseFolder)
                ->sortDesc()
                ->each(function ($subFolder) {
                    Cloudinary::admin()->deleteFolder($subFolder);
                });


            Cloudinary::admin()->deleteFolder($baseFolder);
        } catch (ApiError $e) {
        }
    }
}
