<?php

namespace DoeAnderson\StatamicCloudinary\Jobs;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use DoeAnderson\StatamicCloudinary\Helpers\CloudinaryHelper;
use Exception;
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
    /**
     * @return int
     */
    public function retryAfter(): int
    {
        return 30;
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        try {
            Cloudinary::admin()->createFolder(
                CloudinaryHelper::getCloudinaryUploadFolder($this->assetFolder->container()) . '/' . $this->assetFolder->path()
            );
        } catch (Exception $e) {
            $message = "Cloudinary: {$e->getMessage()}";
            throw new Exception($message, 0, $e);
        }
    }
}
