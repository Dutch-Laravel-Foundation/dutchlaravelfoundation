<?php

declare(strict_types=1);

use App\ResponseCache\CspNonceReplacer;
use App\ResponseCache\SetCookieHeaderReplacer;
use Symfony\Component\HttpFoundation\Response;

it('replaces CSP nonces in cached HTML, JSON, and headers', function (): void {
    app()->instance('csp-nonce', 'first-request-nonce');

    $response = new Response(
        '<script nonce="first-request-nonce">window.test = true;</script>'.
        '<script type="application/json">{"cspNonce":"first-request-nonce"}</script>',
        200,
        ['Content-Security-Policy' => "script-src 'nonce-first-request-nonce'"],
    );
    $replacer = new CspNonceReplacer;

    $replacer->prepareResponseToCache($response);

    expect($response->getContent())->not->toContain('first-request-nonce')
        ->and($response->headers->get('Content-Security-Policy'))->not->toContain('first-request-nonce');

    app()->instance('csp-nonce', 'cache-hit-nonce');
    $replacer->replaceInCachedResponse($response);

    expect($response->getContent())->toContain('nonce="cache-hit-nonce"')
        ->and($response->getContent())->toContain('"cspNonce":"cache-hit-nonce"')
        ->and($response->headers->get('Content-Security-Policy'))->toContain("'nonce-cache-hit-nonce'");
});

it('does not store session cookies in a shared response', function (): void {
    $response = new Response('Cached page');
    $response->headers->setCookie(cookie('dlf_session', 'private-session'));
    $replacer = new SetCookieHeaderReplacer;

    $replacer->prepareResponseToCache($response);

    expect($response->headers->has('Set-Cookie'))->toBeFalse();

    $replacer->replaceInCachedResponse($response);

    expect($response->headers->has('Set-Cookie'))->toBeFalse();
});
