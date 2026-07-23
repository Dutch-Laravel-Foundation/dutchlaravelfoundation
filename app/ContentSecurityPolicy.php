<?php

declare(strict_types=1);

namespace App;

use Illuminate\Foundation\Vite;
use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;
use Spatie\Csp\Scheme;
use Spatie\Csp\Value;

final readonly class ContentSecurityPolicy implements Preset
{
    public function __construct(private Vite $vite)
    {
    }

    public function configure(Policy $policy): void
    {
        $policy
            ->add(Directive::DEFAULT, Keyword::SELF)
            ->add(Directive::BASE, Keyword::SELF)
            ->add(Directive::FORM_ACTION, Keyword::SELF)
            ->add(Directive::FRAME_ANCESTORS, Keyword::SELF)
            ->add(Directive::OBJECT, Keyword::NONE)
            ->add(Directive::FONT, [Keyword::SELF, Scheme::DATA])
            ->add(Directive::MEDIA, Keyword::SELF)
            ->add(Directive::MANIFEST, Keyword::SELF)
            ->add(Directive::SCRIPT, [
                Keyword::SELF,
                'https://www.googletagmanager.com',
                'https://cdn.leadinfo.net',
                'https://snap.licdn.com',
                'https://challenges.cloudflare.com',
                'https://app.vragen.ai',
                'https://dlf.vragen.ai',
            ])
            ->addNonce(Directive::SCRIPT)
            ->add(Directive::STYLE, [Keyword::SELF, 'https://dlf.vragen.ai'])
            ->addNonce(Directive::STYLE)
            // Statamic content and existing Antlers components render style attributes.
            // Keep the exception scoped to attributes; style elements still require a nonce.
            ->add(Directive::STYLE_ATTR, Keyword::UNSAFE_INLINE)
            ->add(Directive::IMG, [
                Keyword::SELF,
                Scheme::DATA,
                Scheme::BLOB,
                'https://www.googletagmanager.com',
                'https://www.google-analytics.com',
                'https://px.ads.linkedin.com',
                'https://app.vragen.ai',
                'https://dlf.vragen.ai',
                'https://i.ytimg.com',
            ])
            ->add(Directive::CONNECT, [
                Keyword::SELF,
                'https://www.googletagmanager.com',
                'https://www.google-analytics.com',
                'https://region1.google-analytics.com',
                'https://api.leadinfo.com',
                'https://collector.leadinfo.net',
                'https://collector4.leadinfo.net',
                'https://px.ads.linkedin.com',
                'https://app.vragen.ai',
                'https://dlf.vragen.ai',
            ])
            ->add(Directive::FRAME, [
                'https://challenges.cloudflare.com',
                'https://www.youtube.com',
                'https://www.youtube-nocookie.com',
                'https://player.vimeo.com',
                'https://open.spotify.com',
            ])
            ->add(Directive::UPGRADE_INSECURE_REQUESTS, Value::NO_VALUE);

        $this->allowHotReloading($policy);
    }

    private function allowHotReloading(Policy $policy): void
    {
        if (! $this->vite->isRunningHot()) {
            return;
        }

        $hotUrl = trim((string) file_get_contents($this->vite->hotFile()));
        $hotOrigin = parse_url($hotUrl, PHP_URL_SCHEME).'://'.parse_url($hotUrl, PHP_URL_HOST);
        $hotPort = parse_url($hotUrl, PHP_URL_PORT);

        if ($hotPort !== null) {
            $hotOrigin .= ":{$hotPort}";
        }

        $policy->add([
            Directive::CONNECT,
            Directive::FONT,
            Directive::SCRIPT,
            Directive::STYLE,
        ], $hotOrigin);
    }
}
