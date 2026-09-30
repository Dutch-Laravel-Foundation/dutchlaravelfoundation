<?php

declare(strict_types=1);

namespace App\Content\Community;

use App\Data\Community\AssetData;
use App\Data\Community\CallToActionData;
use App\Data\Community\CaseCardData;
use App\Data\Community\CaseData;
use App\Data\Community\CaseIndexData;
use App\Data\Community\ClientData;
use App\Data\Community\ContentBlockData;
use App\Data\Community\ContentColumnsData;
use App\Data\Community\InternshipCardData;
use App\Data\Community\InternshipContactData;
use App\Data\Community\InternshipData;
use App\Data\Community\InternshipFiltersData;
use App\Data\Community\InternshipIndexData;
use App\Data\Community\LarabellesData;
use App\Data\Community\LinkData;
use App\Data\Community\MemberData;
use App\Data\Community\MemberFiltersData;
use App\Data\Community\MemberIndexData;
use App\Data\Community\MemberSummaryData;
use App\Data\Community\PageData;
use App\Data\SeoData;

final class CommunityDataMapper
{
    /** @param array<string, mixed> $response */
    public function mapCaseIndex(array $response): CaseIndexData
    {
        return new CaseIndexData(
            page: $this->mapPage($response['page'] ?? null),
            items: $this->mapList(
                $this->connectionData($response, 'entries'),
                fn (array $entry): CaseCardData => $this->mapCaseCard($entry),
            ),
        );
    }

    /** @param array<string, mixed>|null $entry */
    public function mapCase(?array $entry): ?CaseData
    {
        if ($entry === null) {
            return null;
        }

        return new CaseData(
            id: (string) ($entry['id'] ?? ''),
            title: (string) ($entry['title'] ?? ''),
            displayTitle: $this->displayTitle($entry),
            slug: (string) ($entry['slug'] ?? ''),
            url: $this->nullableString($entry['url'] ?? null),
            uri: $this->nullableString($entry['uri'] ?? null),
            date: $this->nullableString($entry['date'] ?? null),
            introductionHtml: $this->nullableString($entry['introduction'] ?? null),
            featuredImage: $this->mapAsset($entry['featured_image'] ?? null),
            content: $this->mapContent($entry['content'] ?? null),
            member: $this->mapMemberSummary($entry['member'] ?? null),
            client: $this->mapClient($entry['client'] ?? null),
            seo: $this->mapSeo($entry),
        );
    }

    /** @param array<string, mixed> $response */
    public function mapMemberIndex(array $response): MemberIndexData
    {
        $items = $this->mapList(
            $this->connectionData($response, 'entries'),
            fn (array $entry): ?MemberSummaryData => $this->mapMemberSummary($entry),
        );
        usort($items, static fn (MemberSummaryData $left, MemberSummaryData $right): int => strcasecmp($left->title, $right->title));

        return new MemberIndexData(
            page: $this->mapPage($response['page'] ?? null),
            items: $items,
            filters: new MemberFiltersData(
                types: $this->uniqueValues($items, static fn (MemberSummaryData $member): ?string => $member->type),
                employeeRanges: $this->uniqueValues($items, static fn (MemberSummaryData $member): ?string => $member->employeeRange),
                provinces: $this->uniqueValues($items, static fn (MemberSummaryData $member): ?string => $member->province),
            ),
        );
    }

    /** @param array<string, mixed> $response */
    public function mapMember(array $response): ?MemberData
    {
        $member = $response['member'] ?? null;

        if (! is_array($member)) {
            return null;
        }

        return new MemberData(
            id: (string) ($member['id'] ?? ''),
            title: (string) ($member['title'] ?? ''),
            slug: (string) ($member['slug'] ?? ''),
            url: $this->nullableString($member['url'] ?? null),
            uri: $this->nullableString($member['uri'] ?? null),
            descriptionHtml: $this->nullableString($member['description'] ?? null),
            logo: $this->mapAsset($member['logo'] ?? null),
            foundingPartner: (bool) ($member['founding_partner'] ?? false),
            type: $this->mapLabeledValue($member['type'] ?? null),
            employeeRange: $this->mapLabeledValue($member['employees'] ?? null),
            sbb: (bool) ($member['sbb'] ?? false),
            city: $this->nullableString($member['city'] ?? null),
            province: $this->mapLabeledValue($member['province'] ?? null),
            email: $this->nullableString($member['email'] ?? null),
            phone: $this->nullableString($member['phone'] ?? null),
            website: $this->nullableString($member['website'] ?? null),
            recruitmentWebsite: $this->nullableString($member['recruitment_website'] ?? null),
            video: $this->nullableString($member['video'] ?? null),
            internshipContact: $this->mapInternshipContact($member),
            seo: $this->mapSeo($member),
            internships: $this->mapList(
                $this->connectionData($response, 'internships'),
                fn (array $entry): ?InternshipCardData => $this->mapInternshipCard($entry),
            ),
            cases: $this->mapList(
                $this->connectionData($response, 'cases'),
                fn (array $entry): CaseCardData => $this->mapCaseCard($entry),
            ),
        );
    }

    /** @param array<string, mixed> $response */
    public function mapInternshipIndex(array $response): InternshipIndexData
    {
        $items = $this->mapList(
            $this->connectionData($response, 'entries'),
            fn (array $entry): ?InternshipCardData => $this->mapInternshipCard($entry),
        );

        return new InternshipIndexData(
            page: $this->mapPage($response['page'] ?? null),
            items: $items,
            filters: new InternshipFiltersData(
                provinces: $this->uniqueValues($items, static fn (InternshipCardData $internship): ?string => $internship->member->province),
                hasSbb: array_any($items, static fn (InternshipCardData $internship): bool => $internship->member->sbb),
            ),
        );
    }

    /** @param array<string, mixed>|null $entry */
    public function mapInternship(?array $entry): ?InternshipData
    {
        if ($entry === null) {
            return null;
        }

        $member = $this->mapMemberSummary($entry['member'] ?? null);

        if ($member === null) {
            return null;
        }

        $applyLink = $this->mapLink($entry['apply_url'] ?? null);

        return new InternshipData(
            id: (string) ($entry['id'] ?? ''),
            title: (string) ($entry['title'] ?? ''),
            slug: (string) ($entry['slug'] ?? ''),
            url: $this->nullableString($entry['url'] ?? null),
            uri: $this->nullableString($entry['uri'] ?? null),
            descriptionHtml: $this->nullableString($entry['description'] ?? null),
            applyUrl: $applyLink?->url,
            applyLabel: $applyLink?->title,
            member: $member,
            seo: $this->mapSeo($entry),
        );
    }

    /** @param array<string, mixed>|null $entry */
    public function mapLarabelles(?array $entry): ?LarabellesData
    {
        if ($entry === null) {
            return null;
        }

        return new LarabellesData(page: $this->mapPage($entry));
    }

    private function mapPage(mixed $value): PageData
    {
        $page = is_array($value) ? $value : [];

        return new PageData(
            id: (string) ($page['id'] ?? ''),
            title: (string) ($page['title'] ?? ''),
            slug: (string) ($page['slug'] ?? ''),
            url: $this->nullableString($page['url'] ?? null),
            uri: $this->nullableString($page['uri'] ?? null),
            template: $this->nullableString($page['template'] ?? null),
            content: $this->mapContent($page['content'] ?? null),
            callToAction: $this->mapCallToAction($page['call_to_action'] ?? null),
            seo: $this->mapSeo($page),
        );
    }

    /** @param array<string, mixed> $entry */
    private function mapCaseCard(array $entry): CaseCardData
    {
        return new CaseCardData(
            id: (string) ($entry['id'] ?? ''),
            title: (string) ($entry['title'] ?? ''),
            displayTitle: $this->displayTitle($entry),
            slug: (string) ($entry['slug'] ?? ''),
            url: $this->nullableString($entry['url'] ?? null),
            uri: $this->nullableString($entry['uri'] ?? null),
            date: $this->nullableString($entry['date'] ?? null),
            introductionHtml: $this->nullableString($entry['introduction'] ?? null),
            featuredImage: $this->mapAsset($entry['featured_image'] ?? null),
            member: $this->mapMemberSummary($entry['member'] ?? null),
            client: $this->mapClient($entry['client'] ?? null),
        );
    }

    private function mapMemberSummary(mixed $value): ?MemberSummaryData
    {
        if (! is_array($value)) {
            return null;
        }

        return new MemberSummaryData(
            id: (string) ($value['id'] ?? ''),
            title: (string) ($value['title'] ?? ''),
            slug: (string) ($value['slug'] ?? ''),
            url: $this->nullableString($value['url'] ?? null),
            uri: $this->nullableString($value['uri'] ?? null),
            logo: $this->mapAsset($value['logo'] ?? null),
            type: $this->mapLabeledValue($value['type'] ?? null),
            employeeRange: $this->mapLabeledValue($value['employees'] ?? null),
            sbb: (bool) ($value['sbb'] ?? false),
            city: $this->nullableString($value['city'] ?? null),
            province: $this->mapLabeledValue($value['province'] ?? null),
            website: $this->nullableString($value['website'] ?? null),
            internshipContact: $this->mapInternshipContact($value),
        );
    }

    private function mapClient(mixed $value): ?ClientData
    {
        if (! is_array($value)) {
            return null;
        }

        return new ClientData(
            id: (string) ($value['id'] ?? ''),
            title: (string) ($value['title'] ?? ''),
            slug: (string) ($value['slug'] ?? ''),
            url: $this->nullableString($value['url'] ?? null),
            uri: $this->nullableString($value['uri'] ?? null),
            logo: $this->mapAsset($value['logo'] ?? null),
        );
    }

    /** @param array<string, mixed> $entry */
    private function mapInternshipCard(array $entry): ?InternshipCardData
    {
        $member = $this->mapMemberSummary($entry['member'] ?? null);

        if ($member === null) {
            return null;
        }

        $applyLink = $this->mapLink($entry['apply_url'] ?? null);

        return new InternshipCardData(
            id: (string) ($entry['id'] ?? ''),
            title: (string) ($entry['title'] ?? ''),
            slug: (string) ($entry['slug'] ?? ''),
            url: $this->nullableString($entry['url'] ?? null),
            uri: $this->nullableString($entry['uri'] ?? null),
            descriptionHtml: $this->nullableString($entry['description'] ?? null),
            applyUrl: $applyLink?->url,
            applyLabel: $applyLink?->title,
            member: $member,
        );
    }

    /** @return array<int, ContentBlockData> */
    private function mapContent(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return $this->mapList($value, function (array $block): ContentBlockData {
            $kind = (string) ($block['__typename'] ?? '');
            $columns = null;

            if ($kind === 'Set_Content_DoubleColumn') {
                $columns = new ContentColumnsData(
                    headingHtml: $this->nullableString($block['heading'] ?? null),
                    left: $this->mapContent($block['left'] ?? null),
                    right: $this->mapContent($block['right'] ?? null),
                );
            }

            return new ContentBlockData(
                kind: $kind,
                type: (string) ($block['type'] ?? ''),
                id: $this->nullableString($block['id'] ?? null),
                html: $this->nullableString($block['text'] ?? $block['note'] ?? null),
                value: $this->firstString($block, ['video', 'spacer', 'line']),
                asset: $this->mapAsset($block['image'] ?? null),
                columns: $columns,
            );
        });
    }

    private function mapAsset(mixed $value): ?AssetData
    {
        if (! is_array($value)) {
            return null;
        }

        return new AssetData(
            id: (string) ($value['id'] ?? ''),
            url: $this->nullableString($value['url'] ?? null),
            permalink: $this->nullableString($value['permalink'] ?? null),
            path: (string) ($value['path'] ?? ''),
            extension: (string) ($value['extension'] ?? ''),
            width: $this->nullableInteger($value['width'] ?? null),
            height: $this->nullableInteger($value['height'] ?? null),
            focusCss: $this->nullableString($value['focus_css'] ?? null),
            alt: $this->nullableString($value['alt'] ?? null),
        );
    }

    private function mapCallToAction(mixed $value): ?CallToActionData
    {
        if (! is_array($value)) {
            return null;
        }

        $benefits = array_values(array_filter(
            $value['benefits'] ?? [],
            static fn (mixed $benefit): bool => is_string($benefit),
        ));

        return new CallToActionData(
            id: (string) ($value['id'] ?? ''),
            title: (string) ($value['title'] ?? ''),
            descriptionHtml: $this->nullableString($value['description'] ?? null),
            eyebrow: $this->nullableString($value['eyebrow'] ?? null),
            benefits: $benefits,
            primaryLink: $this->mapLink($value['link'] ?? null),
            secondaryLink: $this->mapLink($value['link_2'] ?? null),
            theme: $this->mapLabeledValue($value['theme'] ?? null),
            buttonText: $this->nullableString($value['button_text'] ?? null),
            buttonStyle: $this->mapLabeledValue($value['button_style'] ?? null),
            secondaryButtonText: $this->nullableString($value['button_text_2'] ?? null),
            secondaryButtonStyle: $this->mapLabeledValue($value['button_style_2'] ?? null),
        );
    }

    private function mapLink(mixed $value): ?LinkData
    {
        if (! is_array($value)) {
            return null;
        }

        $url = $this->nullableString($value['url'] ?? null);

        if ($url === null) {
            return null;
        }

        return new LinkData($url, $this->nullableString($value['title'] ?? null));
    }

    /** @param array<string, mixed> $entry */
    private function mapInternshipContact(array $entry): ?InternshipContactData
    {
        $name = $this->nullableString($entry['internship_contact_name'] ?? null);
        $email = $this->nullableString($entry['internship_contact_email'] ?? null);
        $phone = $this->nullableString($entry['internship_contact_phone'] ?? null);

        if ($name === null && $email === null && $phone === null) {
            return null;
        }

        return new InternshipContactData($name, $email, $phone);
    }

    /** @param array<string, mixed> $entry */
    private function mapSeo(array $entry): SeoData
    {
        return new SeoData(
            title: $this->nullableString($entry['meta_title'] ?? null),
            description: $this->nullableString($entry['meta_description'] ?? null),
            keywords: $this->nullableString($entry['meta_keywords'] ?? null),
        );
    }

    /** @param array<string, mixed> $entry */
    private function displayTitle(array $entry): string
    {
        return $this->nullableString($entry['title_long'] ?? null)
            ?? (string) ($entry['title'] ?? '');
    }

    private function mapLabeledValue(mixed $value): ?string
    {
        if (is_array($value)) {
            return $this->nullableString($value['label'] ?? $value['value'] ?? null);
        }

        return $this->nullableString($value);
    }

    /**
     * @param  array<string, mixed>  $response
     * @return array<int, mixed>
     */
    private function connectionData(array $response, string $key): array
    {
        $connection = $response[$key] ?? null;

        if (! is_array($connection) || ! is_array($connection['data'] ?? null)) {
            return [];
        }

        return $connection['data'];
    }

    /**
     * @template Input
     * @template Output
     *
     * @param  array<int, Input>  $items
     * @param  callable(array<string, mixed>): (Output|null)  $mapper
     * @return array<int, Output>
     */
    private function mapList(array $items, callable $mapper): array
    {
        $mapped = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $result = $mapper($this->stringKeyed($item));

            if ($result !== null) {
                $mapped[] = $result;
            }
        }

        return $mapped;
    }

    /**
     * @template Item
     *
     * @param  array<int, Item>  $items
     * @param  callable(Item): (?string)  $value
     * @return array<int, string>
     */
    private function uniqueValues(array $items, callable $value): array
    {
        $values = [];

        foreach ($items as $item) {
            $candidate = $value($item);

            if ($candidate !== null) {
                $values[$candidate] = $candidate;
            }
        }

        $values = array_values($values);
        natcasesort($values);

        return array_values($values);
    }

    /**
     * @param  array<array-key, mixed>  $values
     * @return array<string, mixed>
     */
    private function stringKeyed(array $values): array
    {
        $stringKeyed = [];

        foreach ($values as $key => $value) {
            if (is_string($key)) {
                $stringKeyed[$key] = $value;
            }
        }

        return $stringKeyed;
    }

    /**
     * @param  array<string, mixed>  $values
     * @param  array<int, string>  $keys
     */
    private function firstString(array $values, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $this->nullableString($values[$key] ?? null);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function nullableInteger(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
