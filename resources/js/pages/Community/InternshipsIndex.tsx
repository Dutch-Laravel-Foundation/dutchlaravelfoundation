import { useEffect, useMemo, useState } from "react";

import { DirectoryFilterDialog } from "@/components/community-react/DirectoryFilterDialog";
import { InternshipCard } from "@/components/community-react/DirectoryCards";
import { plainText } from "@/components/editorial-react/format";
import { SiteLayout } from "@/components/site";

type InternshipsIndexProps = {
    community: App.Data.Community.InternshipIndexData;
    site: App.Data.SiteShell.SiteShellData;
};

type Filters = {
    provinces: string[];
    sbb: string[];
};

const emptyFilters = (): Filters => ({ provinces: [], sbb: [] });

function shuffled<T>(values: readonly T[]): T[] {
    const items = [...values];

    for (let index = items.length - 1; index > 0; index -= 1) {
        const randomIndex = Math.floor(Math.random() * (index + 1));

        [items[index], items[randomIndex]] = [items[randomIndex], items[index]];
    }

    return items;
}

export default function InternshipsIndex({ community, site }: InternshipsIndexProps) {
    const { page } = community;
    const columns = page.content.find((block) => block.type === "double_column")?.columns;
    const heading = plainText(columns?.headingHtml) || page.title;
    const introduction = [...(columns?.left ?? []), ...(columns?.right ?? [])]
        .filter((block) => block.type === "text" && block.html)
        .map((block) => block.html)
        .join("");
    const [internships, setInternships] = useState<
        readonly App.Data.Community.InternshipCardData[]
    >(community.items);
    const [filters, setFilters] = useState<Filters>(emptyFilters);

    useEffect(() => {
        setInternships(shuffled(community.items));
    }, [community.items]);

    const activeFilterCount = filters.provinces.length + filters.sbb.length;
    const filteredInternships = useMemo(
        () =>
            internships.filter((internship) => {
                const provinceMatches =
                    filters.provinces.length === 0 ||
                    (internship.member.province !== null &&
                        filters.provinces.includes(internship.member.province));
                const sbbMatches = filters.sbb.length === 0 || internship.member.sbb;

                return provinceMatches && sbbMatches;
            }),
        [filters, internships],
    );
    const resultLabel = `${String(filteredInternships.length).padStart(2, "0")}${filteredInternships.length === 1 ? " stage" : " stages"}`;

    const toggleFilter = (group: keyof Filters, value: string) => {
        setFilters((current) => ({
            ...current,
            [group]: current[group].includes(value)
                ? current[group].filter((item) => item !== value)
                : [...current[group], value],
        }));
    };

    return (
        <SiteLayout data={site} pageSlug={page.slug}>

            <div className="dlf-community-page dlf-internships-page" data-dlf-footer-cta-stage>
                <section
                    className="dlf-community-section dlf-divider-section"
                    aria-labelledby="internships-heading"
                >
                    <div className="dlf-community-head">
                        <div className="dlf-members-intro">
                            <div>
                                <span className="dlf-community-kicker">{page.title}</span>
                                <h1 id="internships-heading" className="dlf-community-title">
                                    {heading}
                                </h1>
                            </div>
                            {introduction ? (
                                <div
                                    className="dlf-members-intro__copy dlf-community-copy"
                                    data-cms-html
                                    dangerouslySetInnerHTML={{ __html: introduction }}
                                />
                            ) : null}
                        </div>
                    </div>
                </section>

                <section
                    className="dlf-community-section dlf-divider-section"
                    aria-label="Stages zoeken en filteren"
                >
                    <div className="dlf-members-toolbar">
                        <div className="dlf-members-result-count" aria-live="polite">
                            <span>{resultLabel}</span>
                        </div>
                        {community.filters.provinces.length > 1 || community.filters.hasSbb ? (
                            <DirectoryFilterDialog
                                activeCount={activeFilterCount}
                                applyLabel={`Toon ${resultLabel}`}
                                groups={[
                                    ...(community.filters.provinces.length > 1
                                        ? [
                                              {
                                                  active: filters.provinces,
                                                  label: "Provincie",
                                                  options: community.filters.provinces,
                                                  onToggle: (value: string) =>
                                                      toggleFilter("provinces", value),
                                              },
                                          ]
                                        : []),
                                    ...(community.filters.hasSbb
                                        ? [
                                              {
                                                  active: filters.sbb,
                                                  label: "Erkenning",
                                                  options: ["SBB erkend"],
                                                  onToggle: (value: string) =>
                                                      toggleFilter("sbb", value),
                                              },
                                          ]
                                        : []),
                                ]}
                                id="internship-filter-dialog"
                                onClear={() => setFilters(emptyFilters())}
                                title="Wij helpen je zoeken!"
                                triggerClassName="ml-auto"
                            />
                        ) : null}
                    </div>
                </section>

                {!filteredInternships.length ? (
                    <div className="dlf-members-empty" aria-live="polite">
                        Er zijn geen stages gevonden die voldoen aan je zoekopdracht.
                    </div>
                ) : (
                    <section className="dlf-divider-section" aria-label="Beschikbare stages">
                        <div className="dlf-members-grid dlf-fill-grid dlf-divider-grid dlf-divider-grid--fill dlf-divider-grid--desktop-4 dlf-divider-grid--tablet-2 dlf-divider-grid--mobile-2">
                            {filteredInternships.map((internship) => (
                                <InternshipCard internship={internship} key={internship.id} />
                            ))}
                            <span className="dlf-divider-grid__filler" aria-hidden="true" />
                        </div>
                    </section>
                )}
            </div>
        </SiteLayout>
    );
}
