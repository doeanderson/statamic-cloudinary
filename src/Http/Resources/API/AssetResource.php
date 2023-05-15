<?php

namespace DoeAnderson\StatamicCloudinary\Http\Resources\API;

use Cloudinary;
use DoeAnderson\StatamicCloudinary\Helpers\CloudinaryHelper;
use Statamic\Assets\Asset;

/**
 * @property Asset $resource
 */
class AssetResource extends \Statamic\Http\Resources\API\AssetResource
{
    public function toArray($request): array
    {
        $data = parent::toArray($request);

        $cloudinaryPublicId = CloudinaryHelper::getPublicId($this->resource);

        if (! empty($cloudinaryPublicId)) {
            $image = Cloudinary::getImage($cloudinaryPublicId);
            $data['cloudinary_url'] = (string) $image->toUrl();

            // TODO: Add 'srcset' and 'sizes' values based on plugin config.
        }

        return $data;
    }
}
