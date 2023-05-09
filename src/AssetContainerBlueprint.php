<?php

namespace DoeAnderson\StatamicCloudinary;

use Statamic\Fields\Blueprint;

class AssetContainerBlueprint
{
    /**
     * @var Blueprint
     */
    protected $blueprint;

    /**
     * @param Blueprint $blueprint
     */
    public function __construct(Blueprint $blueprint)
    {
        $this->blueprint = $blueprint;
    }

    public function setupFields()
    {
        $this->blueprint->ensureFieldInTab(
            'cloudinary_public_id',
            [
                'input_type' => 'text',
                'display' => 'Cloudinary Public Id',
                'type' => 'text',
                'read_only' => true,
            ],
            'Cloudinary'
        );
    }

}
