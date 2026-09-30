<?php

declare(strict_types=1);

namespace App\Content\Mappers;

use App\Data\Pages\HomePageData;
use App\Data\SeoData;
use App\Data\SiteShell\CtaData;
use App\Data\SiteShell\LabeledValueData;
use App\Data\SiteShell\LinkData;

final class HomePageDataMapper
{
    /**
     * @param  array{entry: array<string, mixed>}  $response
     */
    public function map(array $response): HomePageData
    {
        $entry = $response['entry'];
        $menuColor = $entry['menu_color'] ?? null;

        return new HomePageData(
            id: (string) $entry['id'],
            title: (string) $entry['title'],
            slug: (string) $entry['slug'],
            uri: (string) $entry['uri'],
            headerTitle: $this->nullableString($entry['header_title'] ?? null),
            headerContent: $this->nullableString($entry['header_content'] ?? null),
            menuTheme: is_array($menuColor)
                ? $this->nullableString($menuColor['value'] ?? null)
                : null,
            footerCta: $this->cta($entry['call_to_action'] ?? null),
            seo: new SeoData(
                title: $this->nullableString($entry['meta_title'] ?? null),
                description: $this->nullableString($entry['meta_description'] ?? null),
                keywords: $this->nullableString($entry['meta_keywords'] ?? null),
            ),
        );
    }

    private function cta(mixed $value): ?CtaData
    {
        if (! is_array($value)) {
            return null;
        }

        return new CtaData(
            id: (string) ($value['id'] ?? ''),
            title: (string) ($value['title'] ?? ''),
            description: $this->nullableString($value['description'] ?? null),
            eyebrow: $this->nullableString($value['eyebrow'] ?? null),
            benefits: array_values(array_filter(
                is_array($value['benefits'] ?? null) ? $value['benefits'] : [],
                'is_string',
            )),
            link: $this->link($value['link'] ?? null),
            secondaryLink: $this->link($value['link_2'] ?? null),
            theme: $this->option($value['theme'] ?? null),
            buttonStyle: $this->option($value['button_style'] ?? null),
            secondaryButtonStyle: $this->option($value['button_style_2'] ?? null),
            buttonText: $this->nullableString($value['button_text'] ?? null),
            secondaryButtonText: $this->nullableString($value['button_text_2'] ?? null),
        );
    }

    private function link(mixed $value): ?LinkData
    {
        if (! is_array($value) || ! is_string($value['url'] ?? null) || $value['url'] === '') {
            return null;
        }

        return new LinkData(
            url: $value['url'],
            title: $this->nullableString($value['title'] ?? null),
        );
    }

    private function option(mixed $value): ?LabeledValueData
    {
        if (! is_array($value) || ! is_string($value['value'] ?? null)) {
            return null;
        }

        return new LabeledValueData(
            value: $value['value'],
            label: is_string($value['label'] ?? null) ? $value['label'] : $value['value'],
        );
    }

    private function nullableString(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
