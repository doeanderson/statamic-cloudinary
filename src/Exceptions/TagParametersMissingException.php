<?php

namespace DoeAnderson\StatamicCloudinary\Exceptions;

use Exception;

class TagParametersMissingException extends Exception
{
    public static function create(array $missingParameters): self
    {
        $requiredParams = join(', ', $missingParameters);
        return new self("Missing 1 or more required parameters: {$requiredParams}");
    }
}
