<?php

declare(strict_types=1);

namespace App\Tags;

use Statamic\Tags\Tags;

final class CspNonce extends Tags
{
    protected static $handle = 'csp_nonce';

    public function index(): string
    {
        return (string) app('csp-nonce');
    }
}
