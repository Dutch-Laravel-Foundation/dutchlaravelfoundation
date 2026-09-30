<?php

declare(strict_types=1);

namespace App\Content\SiteShell;

use App\Data\SeoData;
use App\Data\SiteShell\AssetData;
use App\Data\SiteShell\CtaData;
use App\Data\SiteShell\FooterData;
use App\Data\SiteShell\LabeledValueData;
use App\Data\SiteShell\LinkData;
use App\Data\SiteShell\MemberData;
use App\Data\SiteShell\NavigationData;
use App\Data\SiteShell\NavigationItemData;
use App\Data\SiteShell\NewsletterFieldData;
use App\Data\SiteShell\NewsletterFormData;
use App\Data\SiteShell\OpenGraphData;
use App\Data\SiteShell\OrganizationData;
use App\Data\SiteShell\SiteData;
use App\Data\SiteShell\SiteShellData;
use App\Data\SiteShell\SocialData;

final class SiteShellDataMapper
{
    /** @param array<string, mixed> $response */
    public function map(array $response, string $currentUri): SiteShellData
    {
        $organization = $this->array($response['organization'] ?? null);
        $site = $this->array($organization['site'] ?? null);
        $seo = $this->array($response['seo'] ?? null);
        $openGraph = $this->array($response['openGraph'] ?? null);

        return new SiteShellData(
            organization: new OrganizationData(
                // GraphQL's global-set `title` is the control-panel label. The
                // site name is the localized public organization name Antlers exposed.
                title: $this->string($site['name'] ?? $organization['title'] ?? null),
                address: $this->nullableString($organization['address'] ?? null),
                zipcode: $this->nullableString($organization['zipcode'] ?? null),
                city: $this->nullableString($organization['city'] ?? null),
                phone: $this->nullableString($organization['phone'] ?? null),
                email: $this->nullableString($organization['email'] ?? null),
                coc: $this->nullableString($organization['coc'] ?? null),
                logo: $this->mapAsset($organization['logo'] ?? null),
                site: new SiteData(
                    handle: $this->string($site['handle'] ?? null),
                    name: $this->string($site['name'] ?? null),
                    locale: $this->string($site['locale'] ?? null),
                    shortLocale: $this->string($site['short_locale'] ?? null),
                    url: $this->string($site['url'] ?? null),
                ),
            ),
            seo: new SeoData(
                title: $this->nullableString($seo['meta_title'] ?? null),
                description: $this->nullableString($seo['meta_description'] ?? null),
                keywords: $this->nullableString($seo['meta_keywords'] ?? null),
            ),
            openGraph: new OpenGraphData(
                image: $this->mapAsset($openGraph['opengraph_image'] ?? null),
            ),
            navigation: new NavigationData(
                main: $this->mapNavigation(
                    $this->array($this->array($response['mainNavigation'] ?? null)['tree'] ?? null),
                    $currentUri,
                ),
                legal: $this->mapNavigation(
                    $this->array($this->array($response['legalNavigation'] ?? null)['tree'] ?? null),
                    $currentUri,
                    true,
                ),
            ),
            footer: new FooterData(
                members: $this->mapMembers($response['members'] ?? null),
                socials: $this->mapSocials($response['socials'] ?? null),
            ),
            defaultCta: $this->mapCta($response['defaultCta'] ?? null),
            newsletter: $this->mapNewsletter($response['newsletter'] ?? null),
        );
    }

    /**
     * @param  array<int|string, mixed>  $branches
     * @return array<int, NavigationItemData>
     */
    private function mapNavigation(array $branches, string $currentUri, bool $usesRelatedPage = false): array
    {
        $items = [];

        foreach ($branches as $branch) {
            if (! is_array($branch)) {
                continue;
            }

            $page = $this->array($branch['page'] ?? null);
            $destination = $usesRelatedPage
                ? $this->array($page['page'] ?? null)
                : $page;
            $children = $this->mapNavigation(
                $this->array($branch['children'] ?? null),
                $currentUri,
                $usesRelatedPage,
            );
            $isCurrent = $this->urisMatch(
                $currentUri,
                $this->nullableString($destination['url'] ?? null),
            );
            $isAncestor = ! $isCurrent && $this->hasActiveDescendant($children);

            $items[] = new NavigationItemData(
                id: $this->string($page['id'] ?? $destination['id'] ?? null),
                title: $this->nullableString($page['title'] ?? $destination['title'] ?? null),
                slug: $this->nullableString($destination['slug'] ?? null),
                url: $this->nullableString($destination['url'] ?? null),
                permalink: $this->nullableString($destination['permalink'] ?? null),
                isCurrent: $isCurrent,
                isAncestor: $isAncestor,
                children: $children,
            );
        }

        return $items;
    }

    /** @param array<int, NavigationItemData> $children */
    private function hasActiveDescendant(array $children): bool
    {
        foreach ($children as $child) {
            if ($child->isCurrent || $child->isAncestor) {
                return true;
            }
        }

        return false;
    }

    private function urisMatch(string $currentUri, ?string $destination): bool
    {
        if ($destination === null || $destination === '#') {
            return false;
        }

        $currentPath = parse_url($currentUri, PHP_URL_PATH);
        $destinationPath = parse_url($destination, PHP_URL_PATH);

        if (! is_string($currentPath) || ! is_string($destinationPath)) {
            return false;
        }

        return $this->normalizePath($currentPath) === $this->normalizePath($destinationPath);
    }

    private function normalizePath(string $path): string
    {
        $normalized = '/'.trim($path, '/');

        return $normalized === '/' ? '/' : $normalized;
    }

    /** @return array<int, MemberData> */
    private function mapMembers(mixed $value): array
    {
        $members = [];
        $data = $this->array($this->array($value)['data'] ?? null);

        foreach ($data as $member) {
            if (! is_array($member)) {
                continue;
            }

            $members[] = new MemberData(
                id: $this->string($member['id'] ?? null),
                title: $this->string($member['title'] ?? null),
                slug: $this->string($member['slug'] ?? null),
                url: $this->nullableString($member['url'] ?? null),
            );
        }

        return $members;
    }

    /** @return array<int, SocialData> */
    private function mapSocials(mixed $value): array
    {
        $socials = [];
        $data = $this->array($this->array($value)['data'] ?? null);

        foreach ($data as $social) {
            if (! is_array($social)) {
                continue;
            }

            $socials[] = new SocialData(
                id: $this->string($social['id'] ?? null),
                title: $this->string($social['title'] ?? null),
                link: $this->mapLink($social['link'] ?? null),
                icon: $this->mapAsset($social['icon'] ?? null),
            );
        }

        return $socials;
    }

    private function mapCta(mixed $value): ?CtaData
    {
        if (! is_array($value)) {
            return null;
        }

        return new CtaData(
            id: $this->string($value['id'] ?? null),
            title: $this->string($value['title'] ?? null),
            description: $this->nullableString($value['description'] ?? null),
            eyebrow: $this->nullableString($value['eyebrow'] ?? null),
            benefits: $this->stringList($value['benefits'] ?? null),
            link: $this->mapLink($value['link'] ?? null),
            secondaryLink: $this->mapLink($value['link_2'] ?? null),
            theme: $this->mapLabeledValue($value['theme'] ?? null),
            buttonStyle: $this->mapLabeledValue($value['button_style'] ?? null),
            secondaryButtonStyle: $this->mapLabeledValue($value['button_style_2'] ?? null),
            buttonText: $this->nullableString($value['button_text'] ?? null),
            secondaryButtonText: $this->nullableString($value['button_text_2'] ?? null),
        );
    }

    private function mapNewsletter(mixed $value): ?NewsletterFormData
    {
        if (! is_array($value)) {
            return null;
        }

        $fields = [];

        foreach ($this->array($value['fields'] ?? null) as $field) {
            if (! is_array($field)) {
                continue;
            }

            $fields[] = new NewsletterFieldData(
                handle: $this->string($field['handle'] ?? null),
                type: $this->string($field['type'] ?? null),
                display: $this->string($field['display'] ?? null),
                instructions: $this->nullableString($field['instructions'] ?? null),
                width: is_int($field['width'] ?? null) ? $field['width'] : null,
                ifConditions: $this->array($field['if'] ?? null),
                unlessConditions: $this->array($field['unless'] ?? null),
                config: $this->array($field['config'] ?? null),
            );
        }

        return new NewsletterFormData(
            handle: $this->string($value['handle'] ?? null),
            title: $this->string($value['title'] ?? null),
            honeypot: $this->nullableString($value['honeypot'] ?? null),
            rules: $this->array($value['rules'] ?? null),
            fields: $fields,
        );
    }

    private function mapAsset(mixed $value): ?AssetData
    {
        if (! is_array($value)) {
            return null;
        }

        return new AssetData(
            id: $this->string($value['id'] ?? null),
            url: $this->nullableString($value['url'] ?? null),
            permalink: $this->nullableString($value['permalink'] ?? null),
            width: is_numeric($value['width'] ?? null) ? (float) $value['width'] : null,
            height: is_numeric($value['height'] ?? null) ? (float) $value['height'] : null,
        );
    }

    private function mapLink(mixed $value): ?LinkData
    {
        if (! is_array($value)) {
            return null;
        }

        return new LinkData(
            url: $this->nullableString($value['url'] ?? null),
            title: $this->nullableString($value['title'] ?? null),
        );
    }

    private function mapLabeledValue(mixed $value): ?LabeledValueData
    {
        if (! is_array($value)) {
            return null;
        }

        return new LabeledValueData(
            value: $this->nullableString($value['value'] ?? null),
            label: $this->nullableString($value['label'] ?? null),
        );
    }

    /** @return array<int, string> */
    private function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, is_string(...)));
    }

    /** @return array<int|string, mixed> */
    private function array(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    private function nullableString(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }

    private function string(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
