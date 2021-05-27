<?php

namespace DoeAnderson\StatamicCloudinary\Exceptions;

use Exception;

class AssetNotFoundException extends Exception
{
    public static function notExists($assetId): self
    {
        return new self("Could not find asset: {$assetId}");
    }

    public static function notCloudinary($assetId): self
    {
        return new self("This asset is not associated with or uploaded to Cloudinary: {$assetId}");
    }
}
