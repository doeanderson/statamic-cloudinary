<?php

namespace DoeAnderson\StatamicCloudinary;

use DoeAnderson\StatamicCloudinary\Helpers\CloudinaryHelper;
use DoeAnderson\StatamicCloudinary\Jobs\RenameAssetJob;
use DoeAnderson\StatamicCloudinary\Jobs\CreateFolderJob;
use DoeAnderson\StatamicCloudinary\Jobs\DeleteAssetJob;
use DoeAnderson\StatamicCloudinary\Jobs\DeleteFolderJob;
use DoeAnderson\StatamicCloudinary\Jobs\UploadAssetJob;
use Illuminate\Events\Dispatcher;
use Statamic\Events\AssetContainerBlueprintFound;
use Statamic\Events\AssetDeleted;
use Statamic\Events\AssetFolderDeleted;
use Statamic\Events\AssetFolderSaved;
use Statamic\Events\AssetSaved;
use Statamic\Events\AssetUploaded;
use Statamic\Facades\Blink;

class Subscriber
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(AssetContainerBlueprintFound::class, self::class . '@ensureCloudinaryBlueprintFields');
        $events->listen(AssetUploaded::class, self::class . '@handleAssetUploaded');
        $events->listen(AssetDeleted::class, self::class . '@handleAssetDeleted');
        $events->listen(AssetFolderSaved::class, self::class . '@handleAssetFolderSaved');
        $events->listen(AssetFolderDeleted::class, self::class . '@handleAssetFolderDeleted');
        $events->listen(AssetSaved::class, self::class . '@handleAssetRenamed');
    }

    public function ensureCloudinaryBlueprintFields(AssetContainerBlueprintFound $event)
    {
        Blink::once('cloudinary-asset-container-blueprint-fields_' . $event->blueprint->handle(), function () use ($event) {
            if (! CloudinaryHelper::hasConfigurationForAssetContainer($event->container)) {
                return;
            }

            (new AssetContainerBlueprint($event->blueprint))->setupFields();
        });
    }

    public function handleAssetUploaded(AssetUploaded $event)
    {
        if (! CloudinaryHelper::hasConfigurationForAssetContainer($event->asset->container())) {
            return;
        }

        dispatch_now(new UploadAssetJob($event->asset));
    }

    public function handleAssetDeleted(AssetDeleted $event)
    {
        if (CloudinaryHelper::hasConfigurationForAssetContainer($event->asset->container())) {
            dispatch_now(new DeleteAssetJob($event->asset));
        }
    }

    public function handleAssetFolderSaved(AssetFolderSaved $event)
    {
        if (! CloudinaryHelper::hasConfigurationForAssetContainer($event->folder->container())) {
            return;
        }

        dispatch_now(new CreateFolderJob($event->folder));
    }

    public function handleAssetFolderDeleted(AssetFolderDeleted $event)
    {
        if (! CloudinaryHelper::hasConfigurationForAssetContainer($event->folder->container())) {
            return;
        }

        dispatch_now(new DeleteFolderJob($event->folder));
    }

    public function handleAssetRenamed(AssetSaved $event)
    {
        if (! CloudinaryHelper::hasConfigurationForAssetContainer($event->asset->container())) {
            return;
        }

        if (! CloudinaryHelper::hasCloudinaryId($event->asset)) {
            return;
        }

        if (! CloudinaryHelper::isAssetRenamed($event->asset)) {
            return;
        }

        $oldPath = CloudinaryHelper::getPublicId($event->asset) . '.' . CloudinaryHelper::getFileExtension($event->asset);

        dispatch_now(new RenameAssetJob($event->asset, $oldPath));
    }
}
