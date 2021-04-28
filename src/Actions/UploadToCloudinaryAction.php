<?php

namespace DoeAnderson\StatamicCloudinary\Actions;

use DoeAnderson\StatamicCloudinary\Helpers\CloudinaryHelper;
use DoeAnderson\StatamicCloudinary\Jobs\UploadAssetJob;
use Illuminate\Support\Collection;
use Statamic\Actions\Action;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetFolder;

class UploadToCloudinaryAction extends Action
{
    protected $confirm = true;

    protected static $title = 'Upload to Cloudinary';

    public function authorize($user, $item)
    {
        if (! $this->itemIsValid($item)) {
            return false;
        }

        return $user->can("upload {$item->container()->handle()} assets", $item);
    }

    public function buttonText()
    {
        /** @translation */
        return 'Upload to Cloudinary|Upload :count items to Cloudinary';
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Are you sure you want to upload these items to Cloudinary?|Are you sure you want to upload these :count items to Cloudinary?';
    }

    public function visibleTo($item)
    {
        if (! $this->itemIsValid($item)) {
            return false;
        }

        if (! CloudinaryHelper::hasConfigurationForAssetContainer($item->container())) {
            return false;
        }

        return true;
    }

    public function run($items, $values)
    {
        /* @var $items Collection */
        $items->each(function ($item) {
            if ($item instanceof Asset) {
                dispatch(new UploadAssetJob($item));
            }

            if ($item instanceof AssetFolder) {
                $item->assets(true)->each(function (Asset $asset) {
                    dispatch(new UploadAssetJob($asset));
                });
            }
        });

        return true;
    }

    /**
     * @param $item
     * @return bool
     */
    protected function itemIsValid($item)
    {
        return ($item instanceof Asset || $item instanceof AssetFolder);
    }
}
