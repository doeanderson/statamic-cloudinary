<?php

namespace DoeAnderson\StatamicCloudinary\GraphQL;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use DoeAnderson\StatamicCloudinary\Helpers\CloudinaryHelper;
use Statamic\Assets\Asset;
use Statamic\Facades\GraphQL;

class FieldManager
{
    /**
     * Register custom GraphQL fields.
     */
    public function registerCustomFields(): void
    {
        $this->cloudinaryUrl();
        $this->cloudinaryPublicId();
    }

    /**
     * Register 'cloudinary_url' field.
     *
     * @return $this
     */
    protected function cloudinaryUrl(): self
    {
        GraphQL::addField('AssetInterface', 'cloudinary_url', function () {
            return [
                'type' => GraphQL::string(),
                'args' => [],
                'resolve' => function (Asset $asset, $args) {
                    $image = Cloudinary::getImage(CloudinaryHelper::getPublicId($asset));
                    return (string) $image->toUrl();
                }
            ];
        });

        return $this;
    }

    protected function cloudinaryPublicId(): self
    {
        GraphQL::addField('AssetInterface', 'cloudinary_public_id', function () {
            return [
                'type' => GraphQL::string(),
                'args' => [],
                'resolve' => function (Asset $asset, $args) {
                    return CloudinaryHelper::getPublicId($asset);
                }
            ];
        });

        return $this;
    }
}
