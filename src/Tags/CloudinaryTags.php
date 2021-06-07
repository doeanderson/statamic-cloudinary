<?php

namespace DoeAnderson\StatamicCloudinary\Tags;

use Cloudinary\Tag\ImageTag;
use Cloudinary\Transformation\Compass;
use Cloudinary\Transformation\FocusOn;
use Cloudinary\Transformation\Gravity;
use Cloudinary\Transformation\Resize;
use DoeAnderson\StatamicCloudinary\Exceptions\AssetNotFoundException;
use DoeAnderson\StatamicCloudinary\Exceptions\TransformationModeNotFoundException;
use DoeAnderson\StatamicCloudinary\Exceptions\TagParametersMissingException;
use DoeAnderson\StatamicCloudinary\Helpers\CloudinaryHelper;
use Statamic\Assets\Asset;
use Statamic\Facades\Asset as AssetAPI;
use Statamic\Tags\Tags;
use Statamic\Tags\Trans;

class CloudinaryTags extends Tags
{
    protected static $handle = 'cloudinary';

    /**
     * These parameters are used by this tag internally and should not be rendered as <img> tag attributes.
     *
     * @var string[]
     */
    protected $reservedParams = [
        'asset',
    ];

    public function index()
    {
        return $this->image();
    }

    /**
     * @throws TagParametersMissingException
     * @throws AssetNotFoundException|TransformationModeNotFoundException
     */
    public function image()
    {
        $params = ['asset'];

        $assetId = $this->params->get($params);
        if (empty($assetId)) {
            throw TagParametersMissingException::create($params);
        }

        $asset = AssetAPI::find($assetId);
        if (is_null($asset)) {
            throw AssetNotFoundException::notExists($assetId);
        }

        if (! CloudinaryHelper::hasCloudinaryId($asset)) {
            throw AssetNotFoundException::notCloudinary($assetId);
        }

        return $this->constructImageTag($asset->cloudinary_public_id);
    }

    /**
     * @throws TransformationModeNotFoundException
     */
    protected function constructImageTag(string $cloudinaryId): ImageTag
    {
        /**
         * TODO: Allow adding transformation tags inside of this tag for more flexability, similar to the Vue integration
         *
         * https://cloudinary.com/documentation/php_integration
         * https://cloudinary.com/documentation/transformation_reference
         * https://cloudinary.com/documentation/vue_integration
         */
        $imageTag = new ImageTag($cloudinaryId);

        $this
            ->resize($imageTag)
            ->setAttributesFromParams($imageTag);

        return $imageTag;
    }

    /**
     * @param ImageTag $imageTag
     */
    protected function setAttributesFromParams(ImageTag $imageTag)
    {
        foreach ($this->params as $paramKey => $paramValue) {
            if (in_array($paramKey, $this->reservedParams)) {
                continue;
            }

            $imageTag->setAttribute($paramKey, $paramValue);
        }
    }

    /**
     * @throws TransformationModeNotFoundException
     */
    protected function resize(ImageTag $imageTag): ?self
    {
        $width = $this->params->get('width');
        $height = $this->params->get('height');
        $resizeMode = strtolower($this->params->get('crop', 'scale'));
        $gravityMode = strtolower($this->params->get('gravity', 'auto'));

        if (! in_array($resizeMode, ['scale', 'crop', 'fill'])) {
            throw TransformationModeNotFoundException::create('resize', $resizeMode);
        }

        if ($width || $height) {
            if (in_array($resizeMode, ['fill', 'crop'])) {
                $imageTag->$resizeMode($width, $height, $gravityMode);
            }

            if ($resizeMode === 'scale') {
                $imageTag->$resizeMode($width, $height);
            }
        }

        return $this;
    }
}
