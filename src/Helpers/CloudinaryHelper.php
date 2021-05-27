<?php

namespace DoeAnderson\StatamicCloudinary\Helpers;

use Cloudinary\Api\Exception\ApiError;
use Cloudinary\Asset\File;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary as CloudinaryApi;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;

class CloudinaryHelper
{
    /**
     * @param string $parentFolder
     * @param array $folders
     * @param int $level
     * @return Collection
     * @throws ApiError
     */
    public static function getSubFolders($parentFolder = '', &$folders = [], int $level = 0): Collection
    {
        $level++;
        $subFolders = CloudinaryApi::admin()->subFolders($parentFolder)['folders'];

        foreach ($subFolders as $folder) {
            $folders[] = [
                'level' => $level,
                'path' => $folder['path'],
            ];

            static::getSubFolders($folder['path'], $folders, $level);
        }

        usort($folders, function ($a, $b) {
            return $a['level'] <=> $b['level'];
        });

        return collect(
            array_map(function ($folder) {
                return $folder['path'];
            }, $folders)
        );
    }

    /**
     * Get cloudinary upload folder from config for asset container.
     *
     * @param AssetContainer $assetContainer
     * @return string|null
     */
    public static function getCloudinaryUploadFolder(AssetContainer $assetContainer): ?string
    {
        $mappings = config('statamic.cloudinary.asset_container_mappings');
        if (! is_array($mappings)) {
            return null;
        }

        foreach ($mappings as $mapping) {
            if ($mapping['asset_container'] === $assetContainer->handle()) {
                return (string) Str::of($mapping['cloudinary_media_library_folder'])->after("/");
            }
        }

        return null;
    }

    /**
     * @param Asset $asset
     * @return string
     */
    public static function getPublicId(Asset $asset): string
    {
        return (string) Str::of($asset->path())
            ->beforeLast('.' . static::getFileExtension($asset))
            ->after('./');
    }

    /**
     * Get asset's file extension.
     *
     * @param Asset $asset
     * @return string
     */
    public static function getFileExtension(Asset $asset): string
    {
        return pathinfo($asset->path())['extension'];
    }

    /**
     * Is the file renamed (ie: does the cloudinary path/id match the current path?)
     *
     * @param Asset $asset
     * @return bool
     */
    public static function isAssetRenamed(Asset $asset): bool
    {
        $assetPublicId = $asset->get('cloudinary_public_id');
        if (empty($assetPublicId)) {
            return false;
        }

        return $assetPublicId !== static::getPublicId($asset);
    }

    /**
     * @param Asset $asset
     * @return bool
     */
    public static function hasCloudinaryId(Asset $asset): bool
    {
        return ! is_null($asset->get('cloudinary_public_id'));
    }

    /**
     * Is cloudinary folder mapping configured for the asset's container?
     *
     * @param AssetContainer $assetContainer
     * @return bool
     */
    public static function hasConfigurationForAssetContainer(AssetContainer $assetContainer): bool
    {
        return ! is_null(static::getCloudinaryUploadFolder($assetContainer));
    }
}
