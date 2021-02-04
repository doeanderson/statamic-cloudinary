<?php

namespace DoeAnderson\StatamicCloudinary\Subscribers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Statamic\Events\AssetDeleted;
use Statamic\Events\AssetSaved;
use Statamic\Events\AssetUploaded;

class AssetEventSubscriber
{
    public function subscribe(Dispatcher $events)
    {
//        $events->listen(AssetDeleted::class, self::class . '@handle');
//        $events->listen(AssetUploaded::class, self::class . '@handle');
        $events->listen(AssetSaved::class, self::class . '@handleSaved');
    }

    public function handleSaved($event)
    {
        var_dump($event);
        exit();
    }

}
