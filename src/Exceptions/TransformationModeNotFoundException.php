<?php

namespace DoeAnderson\StatamicCloudinary\Exceptions;

use Exception;

class TransformationModeNotFoundException extends Exception
{
    public static function create(string $transformationName, string $mode): self
    {
        return new self("Cloudinary {$transformationName} transformation mode not found: {$mode}");
    }
}
