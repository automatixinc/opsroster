<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Thrown when tenant owner user tries to change its email to an email of another tenant.
 *
 * @see \App\Models\User
 */
class EmailOccupiedException extends Exception
{
    public function __construct()
    {
        parent::__construct('The email is occupied by another tenant.');
    }
}
