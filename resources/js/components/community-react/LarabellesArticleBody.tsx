import { type ReactNode, useEffect, useRef, useState } from "react";

import { slugify } from "@/components/editorial-react/format";
import { SmartLink } from "@/components/ui/SmartLink";

type TocItem = {
    id: string;
    label: string;
};

export function LarabellesArticleBody({ children }: { children: ReactNode }) {
    const rootRef = useRef<HTMLDivElement>(null);
    const proseRef = useRef<HTMLElement>(null);
    const [activeId, setActiveId] = useState<string>();
    const [toc, setToc] = useState<TocItem[]>([]);

    useEffect(() => {
        const prose = proseRef.current;

        if (!prose) {
            return;
        }

        const usedIds = new Set<string>();
        const headings = [...prose.querySelectorAll<HTMLHeadingElement>("h2")];
        const items = headings.map((heading, index) => {
            const label = heading.textContent?.trim() ?? "";
            const baseId = heading.id || slugify(label) || `onderdeel-${index + 1}`;
            let id = baseId;
            let duplicate = 2;

            while (usedIds.has(id)) {
                id = `${baseId}-${duplicate++}`;
            }

            usedIds.add(id);
            heading.id = id;

            return { id, label };
        });

        setToc(items);

        if (!headings.length) {
            return;
        }

        const updateActive = () => {
            let active = headings[0];

            headings.forEach((heading) => {
                if (heading.getBoundingClientRect().top <= 140) {
                    active = heading;
                }
            });

            setActiveId(active.id);
        };
        const observer =
            "IntersectionObserver" in window
                ? new IntersectionObserver(updateActive, {
                      rootMargin: "-90px 0px -68% 0px",
                      threshold: 0,
                  })
                : null;

        headings.forEach((heading) => observer?.observe(heading));
        window.addEventListener("scroll", updateActive, { passive: true });
        window.addEventListener("hashchange", updateActive);
        updateActive();

        return () => {
            observer?.disconnect();
            window.removeEventListener("scroll", updateActive);
            window.removeEventListener("hashchange", updateActive);
        };
    }, [children]);

    return (
        <div
            ref={rootRef}
            className="editorial-article__body dlf-public-page__body dlf-divider-section"
            data-editorial-article
        >
            <nav className="editorial-toc" aria-label="Inhoudsopgave" hidden={!toc.length}>
                <span className="editorial-toc__label">Op deze pagina</span>
                <ol data-editorial-toc>
                    {toc.map((item) => (
                        <li key={item.id}>
                            <SmartLink
                                href={`#${item.id}`}
                                aria-current={activeId === item.id ? "location" : undefined}
                            >
                                {item.label}
                            </SmartLink>
                        </li>
                    ))}
                </ol>
            </nav>

            <article
                ref={proseRef}
                className="editorial-article__prose dlf-public-page__prose"
                data-editorial-prose
            >
                {children}
            </article>
        </div>
    );
}
