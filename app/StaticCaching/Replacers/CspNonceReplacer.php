<?php

declare(strict_types=1);

namespace App\StaticCaching\Replacers;

use Illuminate\Http\Response;
use Statamic\StaticCaching\Replacer;

final class CspNonceReplacer implements Replacer
{
    private const PLACEHOLDER = 'STATAMIC_CSP_NONCE';

    public function prepareResponseToCache(Response $response, Response $initial): void
    {
        $this->applyNonce($initial, (string) app('csp-nonce'));
        $this->applyNonce($response, self::PLACEHOLDER);
    }

    public function replaceInCachedResponse(Response $response): void
    {
        $this->applyNonce($response, (string) app('csp-nonce'));
    }

    private function applyNonce(Response $response, string $nonce): void
    {
        if (! $content = $response->getContent()) {
            return;
        }

        $content = preg_replace_callback(
            '/<(?:script|style)\b[^>]*>/i',
            static function (array $matches) use ($nonce): string {
                $tag = $matches[0];

                if (preg_match('/\bnonce=(["\']).*?\1/i', $tag)) {
                    return (string) preg_replace(
                        '/\bnonce=(["\']).*?\1/i',
                        "nonce=\"{$nonce}\"",
                        $tag,
                        1,
                    );
                }

                if (str_starts_with(strtolower($tag), '<script') && preg_match('/\bsrc=/i', $tag)) {
                    return $tag;
                }

                return substr($tag, 0, -1)." nonce=\"{$nonce}\">";
            },
            $content,
        );

        if ($content !== null) {
            $response->setContent($content);
        }
    }
}
