import { type ReactNode, useEffect, useRef, useState } from "react";

import { SmartLink } from "@/components/ui/SmartLink";
import { useSyntaxHighlighting } from "@/hooks/useSyntaxHighlighting";

import { slugify } from "./format";
import { enhanceInlineProgressiveMedia } from "./InlineProgressiveMedia";

type TocItem = {
    id: string;
    label: string;
};

type ArticleBodyProps = {
    children?: ReactNode;
    className?: string;
    html?: string | null;
    label: string;
    showToc?: boolean;
};

export function ArticleBody({
    children,
    className,
    html,
    label,
    showToc = true,
}: ArticleBodyProps) {
    const rootRef = useRef<HTMLDivElement>(null);
    const proseRef = useRef<HTMLElement>(null);
    const [activeId, setActiveId] = useState<string>();
    const [toc, setToc] = useState<TocItem[]>([]);
    const [processedHtml, setProcessedHtml] = useState(() => ({
        source: html ?? null,
        value: html ?? null,
    }));
    const renderedHtml = processedHtml.source === (html ?? null) ? processedHtml.value : html;

    useSyntaxHighlighting(proseRef, html ?? children);

    useEffect(() => {
        const prose = proseRef.current;

        if (!prose) {
            return;
        }

        return enhanceInlineProgressiveMedia(prose);
    }, [children, html]);

    useEffect(() => {
        const root = rootRef.current;
        const prose = proseRef.current;

        if (!root || !prose || !showToc) {
            return;
        }

        const lead = root.closest(".editorial-article")?.querySelector(".editorial-article__lead");
        const firstParagraph = prose.querySelector(":scope > p:first-child");
        const normalize = (value: string | null | undefined) => value?.replace(/\s+/g, " ").trim();

        if (
            lead &&
            firstParagraph &&
            normalize(lead.textContent) === normalize(firstParagraph.textContent)
        ) {
            firstParagraph.remove();
        }

        const usedIds = new Set<string>();
        const headings = [...prose.querySelectorAll<HTMLHeadingElement>("h2")];
        const author = root
            .closest(".editorial-article")
            ?.querySelector<HTMLElement>("[data-editorial-author]");
        const sections: HTMLElement[] = author ? [...headings, author] : headings;
        const items = sections.map((section, index) => {
            const label =
                section === author ? "Over de auteur" : (section.textContent?.trim() ?? "");
            const baseId = section.id || slugify(label) || `onderdeel-${index + 1}`;
            let id = baseId;
            let duplicate = 2;

            while (usedIds.has(id)) {
                id = `${baseId}-${duplicate++}`;
            }

            usedIds.add(id);
            section.id = id;

            return { id, label };
        });

        setToc(items);

        // HTML content is rendered through dangerouslySetInnerHTML. React replaces that
        // subtree whenever the component rerenders, so preserve the IDs (and the removed
        // duplicate lead paragraph) in the rendered HTML rather than relying on direct DOM
        // mutations that would otherwise be lost.
        if (html) {
            setProcessedHtml({ source: html, value: prose.innerHTML });
        }

        if (!sections.length) {
            return;
        }

        const updateActive = () => {
            let active = sections[0];

            sections.forEach((section) => {
                if (section.getBoundingClientRect().top <= 140) {
                    active = section;
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

        sections.forEach((section) => observer?.observe(section));
        window.addEventListener("scroll", updateActive, { passive: true });
        window.addEventListener("hashchange", updateActive);
        updateActive();

        return () => {
            observer?.disconnect();
            window.removeEventListener("scroll", updateActive);
            window.removeEventListener("hashchange", updateActive);
        };
    }, [children, html, showToc]);

    return (
        <div
            ref={rootRef}
            className={`editorial-article__body dlf-divider-section${className ? ` ${className}` : ""}`}
            data-editorial-article={showToc ? "" : undefined}
        >
            {showToc ? (
                <nav className="editorial-toc" aria-label="Inhoudsopgave" hidden={!toc.length}>
                    <span className="editorial-toc__label">{label}</span>
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
            ) : null}

            <article
                ref={proseRef}
                className={`editorial-article__prose${className?.includes("editorial-podcast__body") ? " editorial-podcast__content" : ""}`}
                data-editorial-prose
                data-cms-html
                dangerouslySetInnerHTML={html ? { __html: renderedHtml ?? html } : undefined}
            >
                {html ? undefined : children}
            </article>
        </div>
    );
}
