<?php

namespace DoeAnderson\StatamicCloudinary\Helpers;

use Cloudinary\Api\Admin\AdminApi;
use Cloudinary\Api\Exception\ApiError;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary as CloudinaryApi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Cloudinary
{
    /**
     * @var Collection
     */
    protected static $mediaFolderOptions;

    /**
     * Returns list of all media library folders.
     *
     * @return Collection
     * @throws ApiError
     */
    public static function getMediaFolderOptions(): Collection
    {
        // Cache list for 2 minutes to help avoid exceeding API rate limits.
        return Cache::remember('statamic-cloudinary-media-folder-options', 120, function () {
            static::$mediaFolderOptions = collect([
                '/',
            ]);
            static::generateFolderOptions();
            return static::$mediaFolderOptions;
        });
    }

    /**
     * Recursively loop through all media library folders and populate collection.
     *
     * @param string $path
     * @throws ApiError
     */
    protected static function generateFolderOptions(string $path = '')
    {
        $folders = empty($path)
            ? static::adminApi()->rootFolders()['folders']
            : static::adminApi()->subFolders($path)['folders'];

        foreach ($folders as $folder) {
            static::$mediaFolderOptions->add('/' . $folder['path']);
            static::generateFolderOptions($folder['path']);
        }
    }

    /**
     * @return AdminApi
     */
    protected static function adminApi(): AdminApi
    {
        return CloudinaryApi::admin();
    }
}
