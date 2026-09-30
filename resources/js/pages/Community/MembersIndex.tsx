import { Input } from "@base-ui/react/input";
import { useEffect, useMemo, useState } from "react";

import { communityFooterCta } from "@/components/community-react/CommunityFooterCtaAdapter";
import { CommunityButtonLink } from "@/components/community-react/CommunityButton";
import { DirectoryFilterDialog } from "@/components/community-react/DirectoryFilterDialog";
import { MemberCard } from "@/components/community-react/DirectoryCards";
import { SiteLayout } from "@/components/site";
import { SmartLink } from "@/components/ui/SmartLink";

type MembersIndexProps = {
    community: App.Data.Community.MemberIndexData;
    site: App.Data.SiteShell.SiteShellData;
};

type Filters = {
    employees: string[];
    provinces: string[];
    types: string[];
};

const emptyFilters = (): Filters => ({ employees: [], provinces: [], types: [] });

function shuffled<T>(values: readonly T[]): T[] {
    const items = [...values];

    for (let index = items.length - 1; index > 0; index -= 1) {
        const randomIndex = Math.floor(Math.random() * (index + 1));

        [items[index], items[randomIndex]] = [items[randomIndex], items[index]];
    }

    return items;
}

function SearchIcon() {
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 16 16"
            fill="currentColor"
            aria-hidden="true"
        >
            <path
                fillRule="evenodd"
                d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z"
                clipRule="evenodd"
            />
        </svg>
    );
}

export default function MembersIndex({ community, site }: MembersIndexProps) {
    const { page } = community;
    const [members, setMembers] = useState<readonly App.Data.Community.MemberSummaryData[]>(
        community.items,
    );
    const [query, setQuery] = useState("");
    const [debouncedQuery, setDebouncedQuery] = useState("");
    const [filters, setFilters] = useState<Filters>(emptyFilters);

    useEffect(() => {
        setMembers(shuffled(community.items));
    }, [community.items]);

    useEffect(() => {
        const timeout = window.setTimeout(() => setDebouncedQuery(query), 200);

        return () => window.clearTimeout(timeout);
    }, [query]);

    const activeFilterCount =
        filters.types.length + filters.employees.length + filters.provinces.length;
    const filteredMembers = useMemo(() => {
        const needle = debouncedQuery.trim().toLocaleLowerCase("nl");
        const matches = (selected: readonly string[], value: string | null) =>
            selected.length === 0 || (value !== null && selected.includes(value));

        return members.filter(
            (member) =>
                (needle === "" || member.title.toLocaleLowerCase("nl").includes(needle)) &&
                matches(filters.types, member.type) &&
                matches(filters.employees, member.employeeRange) &&
                matches(filters.provinces, member.province),
        );
    }, [debouncedQuery, filters, members]);
    const resultLabel = `${String(filteredMembers.length).padStart(2, "0")} ${filteredMembers.length === 1 ? "lid" : "leden"}`;
    const showMatchBanner =
        debouncedQuery.trim() === "" && activeFilterCount === 0 && filteredMembers.length > 24;
    const firstMembers = showMatchBanner ? filteredMembers.slice(0, 24) : filteredMembers;
    const secondMembers = showMatchBanner ? filteredMembers.slice(24) : [];

    const toggleFilter = (group: keyof Filters, value: string) => {
        setFilters((current) => ({
            ...current,
            [group]: current[group].includes(value)
                ? current[group].filter((item) => item !== value)
                : [...current[group], value],
        }));
    };

    return (
        <SiteLayout
            data={site}
            pageSlug={page.slug}
            footerCta={page.callToAction ? communityFooterCta(page.callToAction) : undefined}
        >

            <div className="dlf-community-page dlf-members-page" data-dlf-footer-cta-stage>
                <section
                    className="dlf-community-section dlf-divider-section"
                    aria-labelledby="members-heading"
                >
                    <div className="dlf-community-head">
                        <div className="dlf-members-intro">
                            <div>
                                <span className="dlf-community-kicker">{page.title}</span>
                                <h1 id="members-heading" className="dlf-community-title">
                                    Maak kennis met onze Laravel experts
                                </h1>
                            </div>
                            <div className="dlf-members-intro__copy">
                                <p className="dlf-community-copy">
                                    Onze leden werken dagelijks met Laravel. Van freelancers en
                                    bureaus tot organisaties die hun eigen digitale producten
                                    ontwikkelen.
                                </p>
                                <p className="dlf-community-copy">
                                    Zoek je een partij om jouw idee te laten ontwikkelen? Via{" "}
                                    <SmartLink href="/aanvraag">match je project</SmartLink> helpen
                                    we je de perfecte match te vinden.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    className="dlf-community-section dlf-divider-section"
                    aria-label="Leden zoeken en filteren"
                >
                    <div className="dlf-members-toolbar">
                        <div
                            className="dlf-members-result-count dlf-members-result-count--desktop"
                            aria-live="polite"
                        >
                            <span>{resultLabel}</span>
                        </div>
                        <label className="dlf-members-search">
                            <span className="sr-only">Zoek een lid op naam</span>
                            <SearchIcon />
                            <Input
                                type="search"
                                placeholder="Zoek een lid op naam"
                                value={query}
                                onChange={(event) => setQuery(event.currentTarget.value)}
                            />
                        </label>
                        <DirectoryFilterDialog
                            activeCount={activeFilterCount}
                            applyLabel={`Toon ${filteredMembers.length} ${filteredMembers.length === 1 ? "lid" : "leden"}`}
                            applyWidth={184}
                            groups={[
                                {
                                    active: filters.types,
                                    label: "Ik zoek een",
                                    options: community.filters.types,
                                    onToggle: (value: string) => toggleFilter("types", value),
                                },
                                {
                                    active: filters.employees,
                                    label: "Aantal Laravel developers",
                                    options: community.filters.employeeRanges,
                                    onToggle: (value: string) => toggleFilter("employees", value),
                                },
                                {
                                    active: filters.provinces,
                                    label: "Provincie",
                                    options: community.filters.provinces,
                                    onToggle: (value: string) => toggleFilter("provinces", value),
                                },
                            ].filter((group) => group.options.length > 1)}
                            id="member-filter-dialog"
                            onClear={() => setFilters(emptyFilters())}
                            title="Kunnen wij je helpen zoeken?"
                            triggerResultLabel={resultLabel}
                        />
                    </div>
                </section>

                {!filteredMembers.length ? (
                    <div className="dlf-members-empty" aria-live="polite">
                        Er zijn geen leden gevonden die voldoen aan je zoekopdracht.
                    </div>
                ) : (
                    <section className="dlf-divider-section" aria-label="Onze leden">
                        <div className="dlf-members-grid dlf-fill-grid dlf-divider-grid dlf-divider-grid--fill dlf-divider-grid--desktop-4 dlf-divider-grid--tablet-2 dlf-divider-grid--mobile-2">
                            {firstMembers.map((member) => (
                                <MemberCard member={member} key={member.id} />
                            ))}
                            <span className="dlf-divider-grid__filler" aria-hidden="true" />
                        </div>

                        {showMatchBanner ? (
                            <div className="dlf-community-red-band dlf-divider-region dlf-divider-theme-inverse">
                                <div className="dlf-community-red-inner dlf-members-match">
                                    <div className="dlf-members-match__copy">
                                        <span
                                            className="dlf-community-kicker"
                                            style={{ color: "rgba(255, 255, 255, .75)" }}
                                        >
                                            Match je project
                                        </span>
                                        <h2>
                                            Twijfel je welke partij het beste past bij jouw project?
                                        </h2>
                                        <p>
                                            Maak het jezelf makkelijk en beantwoord een paar korte
                                            vragen. Wij koppelen je aan één of meerdere leden die
                                            écht goed aansluiten bij jouw wensen en uitdaging.
                                        </p>
                                    </div>
                                    <CommunityButtonLink
                                        href="/aanvraag"
                                        light
                                        style={{ width: 196 }}
                                    >
                                        Match je project
                                    </CommunityButtonLink>
                                </div>
                            </div>
                        ) : null}

                        {secondMembers.length ? (
                            <div className="dlf-members-grid dlf-fill-grid dlf-divider-region dlf-divider-grid dlf-divider-grid--fill dlf-divider-grid--desktop-4 dlf-divider-grid--tablet-2 dlf-divider-grid--mobile-2">
                                {secondMembers.map((member) => (
                                    <MemberCard member={member} key={member.id} />
                                ))}
                                <span className="dlf-divider-grid__filler" aria-hidden="true" />
                            </div>
                        ) : null}
                    </section>
                )}
            </div>
        </SiteLayout>
    );
}
