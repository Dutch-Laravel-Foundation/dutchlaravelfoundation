<?php

declare(strict_types=1);

namespace App\Content\PublicPages;

use App\Data\PublicPages\ActionData;
use App\Data\PublicPages\AssetData;
use App\Data\PublicPages\BoardMemberData;
use App\Data\PublicPages\CallToActionData;
use App\Data\PublicPages\CardData;
use App\Data\PublicPages\ContentBlockData;
use App\Data\PublicPages\FeatureData;
use App\Data\PublicPages\FoundingPartnerData;
use App\Data\PublicPages\LandingCaseData;
use App\Data\PublicPages\LinkData;
use App\Data\PublicPages\LogoData;
use App\Data\PublicPages\PricingPlanData;
use App\Data\PublicPages\PublicPageData;
use App\Data\PublicPages\PublicPageSupportData;
use App\Data\PublicPages\StatData;
use App\Data\SeoData;

final class PublicPageDataMapper
{
    /** @param array<string, mixed> $response */
    public function map(array $response): ?PublicPageData
    {
        $page = $response['page'] ?? null;

        if (! is_array($page) || ($page['__typename'] ?? null) !== 'Entry_Pages_Pages') {
            return null;
        }

        return new PublicPageData(
            id: $this->string($page['id'] ?? null),
            title: $this->string($page['title'] ?? null),
            slug: $this->string($page['slug'] ?? null),
            url: $this->nullableString($page['url'] ?? null),
            uri: $this->nullableString($page['uri'] ?? null),
            template: $this->string($page['template'] ?? null, 'templates/default'),
            menuTheme: $this->mapLabel($page['menu_color'] ?? null),
            headerTitle: $this->nullableString($page['header_title'] ?? null),
            headerContentHtml: $this->nullableString($page['header_content'] ?? null),
            seo: new SeoData(
                title: $this->nullableString($page['meta_title'] ?? null),
                description: $this->nullableString($page['meta_description'] ?? null),
                keywords: $this->nullableString($page['meta_keywords'] ?? null),
            ),
            callToAction: $this->mapCallToAction($page['call_to_action'] ?? null),
            content: $this->mapContent($page['content'] ?? null),
            support: $this->mapSupport($response),
        );
    }

    /** @return array<int, ContentBlockData> */
    private function mapContent(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $blocks = [];

        foreach ($this->rows($value) as $block) {
            $blocks[] = $this->mapContentBlock($block);
        }

        return $blocks;
    }

    /** @param array<string, mixed> $block */
    private function mapContentBlock(array $block): ContentBlockData
    {
        return new ContentBlockData(
            kind: $this->string($block['__typename'] ?? null),
            type: $this->string($block['type'] ?? null),
            id: $this->nullableString($block['id'] ?? null),
            html: $this->nullableString($block['text'] ?? null),
            headingHtml: $this->nullableString($block['column_heading'] ?? $block['heading'] ?? null),
            left: $this->mapContent($block['left'] ?? null),
            right: $this->mapContent($block['right'] ?? null),
            content: $this->mapContent($block['nested_content'] ?? $block['content'] ?? null),
            asset: $this->mapAsset($block['image'] ?? null),
            title: $this->nullableString($block['title'] ?? null),
            text: $this->mapMetaText($block),
            value: $this->mapBlockValue($block),
            label: $this->nullableString($block['label'] ?? null),
            link: $this->mapLink($block['link'] ?? null),
            eyebrow: $this->nullableString($block['eyebrow'] ?? null),
            heading: $this->nullableString($block['heading'] ?? null),
            bodyHtml: $this->mapBody($block),
            introductionHtml: $this->nullableString($block['introduction'] ?? null),
            columns: $this->mapLabel($block['columns'] ?? null),
            headingLevel: $this->mapLabel($block['heading_level'] ?? null),
            imagePosition: $this->mapLabel($block['image_position'] ?? null),
            tone: $this->mapLabel($block['tone'] ?? null),
            primaryAction: $this->mapPrimaryAction($block),
            secondaryAction: $this->mapAction(
                $block['secondary_label'] ?? null,
                $block['secondary_link'] ?? null,
            ),
            features: $this->mapFeatures($block['features'] ?? null),
            cards: $this->mapCards($block['cards'] ?? null),
            stats: $this->mapStats($block['stats'] ?? null),
            logos: $this->mapLogos($block['logos'] ?? null),
            plans: $this->mapPlans($block['plans'] ?? null),
            quote: $this->nullableString($block['quote'] ?? null),
            attributionName: $this->nullableString($block['name'] ?? null),
            attributionRole: $this->nullableString($block['role'] ?? null),
        );
    }

    /** @param array<string, mixed> $block */
    private function mapMetaText(array $block): ?string
    {
        if (($block['type'] ?? null) !== 'meta_block') {
            return null;
        }

        return $this->nullableString($block['content'] ?? null);
    }

    /** @param array<string, mixed> $block */
    private function mapBody(array $block): ?string
    {
        $body = $this->nullableString($block['body'] ?? null);

        if ($body !== null) {
            return $body;
        }

        if (($block['type'] ?? null) === 'red_note') {
            return $this->nullableString($block['content'] ?? null);
        }

        return null;
    }

    /** @param array<string, mixed> $block */
    private function mapBlockValue(array $block): ?string
    {
        foreach (['spacer', 'line', 'video'] as $field) {
            $value = $this->nullableString($block[$field] ?? null);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    /** @param array<string, mixed> $block */
    private function mapPrimaryAction(array $block): ?ActionData
    {
        $label = $block['primary_label'] ?? $block['link_label'] ?? null;
        $link = $block['primary_link'] ?? $block['link'] ?? null;

        return $this->mapAction($label, $link);
    }

    private function mapAction(mixed $label, mixed $link): ?ActionData
    {
        $mappedLabel = $this->nullableString($label);
        $mappedLink = $this->mapLink($link);

        if ($mappedLabel === null || $mappedLink === null) {
            return null;
        }

        return new ActionData(label: $mappedLabel, link: $mappedLink);
    }

    /** @return array<int, FeatureData> */
    private function mapFeatures(mixed $value): array
    {
        $features = [];

        foreach ($this->rows($value) as $feature) {
            $features[] = new FeatureData(
                id: $this->nullableString($feature['id'] ?? null),
                heading: $this->string($feature['heading'] ?? null),
                bodyHtml: $this->nullableString($feature['body'] ?? null),
                icon: $this->mapAsset($feature['icon'] ?? null),
                action: $this->mapAction($feature['link_label'] ?? null, $feature['link'] ?? null),
            );
        }

        return $features;
    }

    /** @return array<int, CardData> */
    private function mapCards(mixed $value): array
    {
        $cards = [];

        foreach ($this->rows($value) as $card) {
            $cards[] = new CardData(
                id: $this->nullableString($card['id'] ?? null),
                eyebrow: $this->nullableString($card['eyebrow'] ?? null),
                heading: $this->string($card['heading'] ?? null),
                bodyHtml: $this->nullableString($card['body'] ?? null),
                image: $this->mapAsset($card['image'] ?? null),
                action: $this->mapAction($card['link_label'] ?? null, $card['link'] ?? null),
            );
        }

        return $cards;
    }

    /** @return array<int, StatData> */
    private function mapStats(mixed $value): array
    {
        $stats = [];

        foreach ($this->rows($value) as $stat) {
            $stats[] = new StatData(
                id: $this->nullableString($stat['id'] ?? null),
                value: $this->string($stat['value'] ?? null),
                label: $this->string($stat['label'] ?? null),
                context: $this->nullableString($stat['context'] ?? null),
            );
        }

        return $stats;
    }

    /** @return array<int, LogoData> */
    private function mapLogos(mixed $value): array
    {
        $logos = [];

        foreach ($this->rows($value) as $logo) {
            $logos[] = new LogoData(
                id: $this->nullableString($logo['id'] ?? null),
                name: $this->string($logo['name'] ?? null),
                asset: $this->mapAsset($logo['logo'] ?? null),
                link: $this->mapLink($logo['link'] ?? null),
            );
        }

        return $logos;
    }

    /** @return array<int, PricingPlanData> */
    private function mapPlans(mixed $value): array
    {
        $plans = [];

        foreach ($this->rows($value) as $plan) {
            $plans[] = new PricingPlanData(
                id: $this->nullableString($plan['id'] ?? null),
                name: $this->string($plan['name'] ?? null),
                price: $this->nullableString($plan['price'] ?? null),
                suffix: $this->nullableString($plan['suffix'] ?? null),
                descriptionHtml: $this->nullableString($plan['description'] ?? null),
                features: $this->mapPlanFeatures($plan['features'] ?? null),
                action: $this->mapAction($plan['button_label'] ?? null, $plan['button_link'] ?? null),
                featured: (bool) ($plan['featured'] ?? false),
            );
        }

        return $plans;
    }

    /** @return array<int, string> */
    private function mapPlanFeatures(mixed $value): array
    {
        $features = [];

        foreach ($this->rows($value) as $feature) {
            $text = $this->nullableString($feature['feature'] ?? null);

            if ($text !== null) {
                $features[] = $text;
            }
        }

        return $features;
    }

    /** @param array<string, mixed> $response */
    private function mapSupport(array $response): PublicPageSupportData
    {
        $members = $response['members'] ?? null;
        $cases = $this->mapLandingCases($this->entries($response, 'landingCases'));

        return new PublicPageSupportData(
            memberCount: is_array($members) ? $this->integer($members['total'] ?? null) : 0,
            board: $this->mapBoard($this->entries($response, 'board')),
            foundingPartners: $this->mapFoundingPartners($this->entries($response, 'foundingPartners')),
            generalLandingCases: $this->orderedCases($cases, PublicPageRepository::GENERAL_LANDING_CASE_SLUGS),
            frameworkLandingCases: $this->orderedCases($cases, PublicPageRepository::FRAMEWORK_LANDING_CASE_SLUGS),
        );
    }

    /**
     * @param  array<int, mixed>  $entries
     * @return array<int, BoardMemberData>
     */
    private function mapBoard(array $entries): array
    {
        $board = [];

        foreach ($this->rows($entries) as $member) {
            $board[] = new BoardMemberData(
                id: $this->string($member['id'] ?? null),
                name: $this->string($member['title'] ?? null),
                function: $this->nullableString($member['function'] ?? null),
                photo: $this->mapAsset($member['photo'] ?? null),
            );
        }

        return $board;
    }

    /**
     * @param  array<int, mixed>  $entries
     * @return array<int, FoundingPartnerData>
     */
    private function mapFoundingPartners(array $entries): array
    {
        $partners = [];

        foreach ($this->rows($entries) as $partner) {
            $partners[] = new FoundingPartnerData(
                id: $this->string($partner['id'] ?? null),
                name: $this->string($partner['title'] ?? null),
                slug: $this->string($partner['slug'] ?? null),
                url: $this->nullableString($partner['url'] ?? null),
                city: $this->nullableString($partner['city'] ?? null),
                province: $this->mapLabel($partner['province'] ?? null),
                logo: $this->mapAsset($partner['logo'] ?? null),
            );
        }

        return $partners;
    }

    /**
     * @param  array<int, mixed>  $entries
     * @return array<string, LandingCaseData>
     */
    private function mapLandingCases(array $entries): array
    {
        $cases = [];

        foreach ($this->rows($entries) as $case) {
            $slug = $this->string($case['slug'] ?? null);

            $cases[$slug] = new LandingCaseData(
                id: $this->string($case['id'] ?? null),
                title: $this->string($case['title'] ?? null),
                longTitle: $this->nullableString($case['title_long'] ?? null),
                slug: $slug,
                url: $this->nullableString($case['url'] ?? null),
                introductionHtml: $this->nullableString($case['introduction'] ?? null),
                featuredImage: $this->mapAsset($case['featured_image'] ?? null),
            );
        }

        return $cases;
    }

    /**
     * @param  array<string, LandingCaseData>  $cases
     * @param  array<int, string>  $slugs
     * @return array<int, LandingCaseData>
     */
    private function orderedCases(array $cases, array $slugs): array
    {
        $ordered = [];

        foreach ($slugs as $slug) {
            if (isset($cases[$slug])) {
                $ordered[] = $cases[$slug];
            }
        }

        return $ordered;
    }

    private function mapCallToAction(mixed $value): ?CallToActionData
    {
        if (! is_array($value) || ($value['__typename'] ?? 'Entry_Cta_Cta') !== 'Entry_Cta_Cta') {
            return null;
        }

        return new CallToActionData(
            id: $this->string($value['id'] ?? null),
            title: $this->string($value['title'] ?? null),
            descriptionHtml: $this->nullableString($value['description'] ?? null),
            eyebrow: $this->nullableString($value['eyebrow'] ?? null),
            benefits: $this->stringList($value['benefits'] ?? null),
            link: $this->mapLink($value['link'] ?? null),
            secondaryLink: $this->mapLink($value['link_2'] ?? null),
            theme: $this->mapLabel($value['theme'] ?? null),
            buttonText: $this->nullableString($value['button_text'] ?? null),
            buttonStyle: $this->mapLabel($value['button_style'] ?? null),
            secondaryButtonText: $this->nullableString($value['button_text_2'] ?? null),
            secondaryButtonStyle: $this->mapLabel($value['button_style_2'] ?? null),
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
            path: $this->string($value['path'] ?? null),
            extension: $this->string($value['extension'] ?? null),
            width: $this->nullableInteger($value['width'] ?? null),
            height: $this->nullableInteger($value['height'] ?? null),
            focusCss: $this->nullableString($value['focus_css'] ?? null),
            alt: $this->nullableString($value['alt'] ?? null),
        );
    }

    private function mapLink(mixed $value): ?LinkData
    {
        if (is_string($value) && $value !== '') {
            return new LinkData(url: $value, title: null);
        }

        if (! is_array($value)) {
            return null;
        }

        $url = $this->nullableString($value['url'] ?? null);

        if ($url === null) {
            return null;
        }

        return new LinkData(
            url: $url,
            title: $this->nullableString($value['title'] ?? null),
        );
    }

    /**
     * @param  array<string, mixed>  $response
     * @return array<int, mixed>
     */
    private function entries(array $response, string $key): array
    {
        $connection = $response[$key] ?? null;

        if (! is_array($connection)) {
            return [];
        }

        $entries = $connection['data'] ?? null;

        return is_array($entries) ? array_values($entries) : [];
    }

    /** @return array<int, array<string, mixed>> */
    private function rows(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $rows = [];

        foreach ($value as $row) {
            if (! is_array($row)) {
                continue;
            }

            $normalized = [];

            foreach ($row as $key => $item) {
                if (is_string($key)) {
                    $normalized[$key] = $item;
                }
            }

            $rows[] = $normalized;
        }

        return $rows;
    }

    /** @return array<int, string> */
    private function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, 'is_string'));
    }

    private function mapLabel(mixed $value): ?string
    {
        if (is_array($value)) {
            return $this->nullableString($value['value'] ?? $value['label'] ?? null);
        }

        return $this->nullableString($value);
    }

    private function nullableString(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }

    private function nullableInteger(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private function string(mixed $value, string $default = ''): string
    {
        return is_string($value) ? $value : $default;
    }

    private function integer(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }
}
