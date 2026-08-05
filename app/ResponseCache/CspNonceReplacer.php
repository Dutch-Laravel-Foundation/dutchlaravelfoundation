<?php

declare(strict_types=1);

namespace App\ResponseCache;

use Spatie\ResponseCache\Replacers\Replacer;
use Symfony\Component\HttpFoundation\Response;

final class CspNonceReplacer implements Replacer
{
    private const PLACEHOLDER = '<laravel-responsecache-csp-nonce-here>';

    public function prepareResponseToCache(Response $response): void
    {
        $this->replaceNonce($response, (string) app('csp-nonce'), self::PLACEHOLDER);
    }

    public function replaceInCachedResponse(Response $response): void
    {
        $this->replaceNonce($response, self::PLACEHOLDER, (string) app('csp-nonce'));
    }

    private function replaceNonce(Response $response, string $search, string $replacement): void
    {
        if ($search === '') {
            return;
        }

        $content = $response->getContent();

        if (is_string($content)) {
            $response->setContent(str_replace($search, $replacement, $content));
        }

        foreach ($response->headers->all() as $name => $values) {
            $response->headers->set(
                $name,
                array_map(
                    static fn (string $value): string => str_replace($search, $replacement, $value),
                    $values,
                ),
            );
        }
    }
}
