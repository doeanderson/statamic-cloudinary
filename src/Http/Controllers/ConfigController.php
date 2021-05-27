<?php

namespace DoeAnderson\StatamicCloudinary\Http\Controllers;

use Cloudinary\Api\Exception\ApiError;
use DoeAnderson\StatamicCloudinary\Helpers\CloudinaryHelper as CloudinaryHelper;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\AssetContainer as AssetContainerApi;
use Statamic\Facades\Blueprint as BlueprintApi;
use Statamic\Fields\Blueprint;
use Statamic\Http\Controllers\Controller;
use Stillat\Proteus\Support\Facades\ConfigWriter;

class ConfigController extends Controller
{
    /**
     * @param Request $request
     * @return View
     * @throws ApiError
     * @throws AuthorizationException
     */
    public function edit(Request $request)
    {
        $this->authorize('configure cloudinary');

        $blueprint = $this->getBlueprint();

        $fields = $blueprint
            ->fields()
            ->addValues([
                'cloudinary_url' => config('cloudinary.cloud_url'),
                'asset_container_mappings' => config('statamic.cloudinary.asset_container_mappings'),
            ])
            ->preProcess();

        return view(
            'cloudinary::config.edit',
            [
                'blueprint' => $blueprint->toPublishArray(),
                'values' => $fields->values(),
                'meta' => $fields->meta(),
                'route' => cp_route('cloudinary.config.update')
            ]
        );
    }

    /**
     * @param Request $request
     * @throws AuthorizationException
     * @throws ApiError
     */
    public function update(Request $request)
    {
        $this->authorize('configure cloudinary');

        $blueprint = $this->getBlueprint();

        $requestValues = $request->all();
        $requestValues['cloudinary_url'] = config('cloudinary.cloud_url');

        $fields = $blueprint
            ->fields()
            ->addValues($requestValues);

        $fields->validate();

        $values = $fields
            ->process()
            ->values()
            ->toArray();

        unset($values['cloudinary_url']);

        ConfigWriter::writeMany('statamic.cloudinary', $values);
    }

    /**
     * @return Blueprint
     * @throws ApiError
     */
    protected function getBlueprint(): Blueprint
    {
        $assetContainerOptions = AssetContainerApi::all()
            ->flatMap(function (AssetContainer $assetContainer) {
                return [$assetContainer->handle() => $assetContainer->title()];
            });

        $mediaFolderOptions = Cache::remember('cloudinary-media-folder-options', 120, function () {
            return CloudinaryHelper::getSubFolders();
        })
            ->prepend('')
            ->map(function ($folder) {
                return '/' . $folder;
            });

        return BlueprintApi::makeFromFields([
            'cloudinary_url' => [
                'input_type' => 'text',
                'display' => 'Cloudinary URL',
                'type' => 'text',
                'read_only' => true,
                'required' => true,
                'instructions' => 'This must be set in the `cloudinary.cloud_url` config or as an env variable `CLOUDINARY_URL`. <br />See docs for more info: https://github.com/cloudinary-labs/cloudinary-laravel',
            ],
            'asset_container_mappings' => [
                'fields' => [
                    [
                        'field' => [
                            'display' => 'Asset Container',
                            'type' => 'select',
                            'required' => true,
                            'options' => $assetContainerOptions->toArray(),
                        ],
                        'handle' => 'asset_container',
                    ],
                    [
                        'field' => [
                            'display' => 'Cloudinary Media Library Folder',
                            'type' => 'select',
                            'required' => true,
                            'options' => $mediaFolderOptions->toArray(),
                        ],
                        'handle' => 'cloudinary_media_library_folder',
                    ],
                ],
                'mode' => 'stacked',
                'type' => 'grid',
                'icon' => 'grid',
                'display' => 'Map Asset Containers to Cloudinary Folders',
                'add_row' => 'Add New Asset Container Mapping',
                'instructions' => 'The settings below will be saved to your `config/statamic/cloudinary.php` file.',
            ],
        ]);
    }
}
