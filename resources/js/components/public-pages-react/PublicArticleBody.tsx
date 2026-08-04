import { type ReactNode, useEffect, useRef, useState } from "react";

import { SmartLink } from "@/components/ui/SmartLink";

type TocItem = { id: string; label: string };

function slugify(value: string) {
    return value
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-|-$/g, "");
}

export function PublicArticleBody({ children, label }: { children: ReactNode; label: string }) {
    const proseRef = useRef<HTMLElement>(null);
    const [activeId, setActiveId] = useState<string>();
    const [items, setItems] = useState<TocItem[]>([]);

    useEffect(() => {
        const prose = proseRef.current;
        if (!prose) return;

        const used = new Set<string>();
        const headings = [...prose.querySelectorAll<HTMLHeadingElement>("h2")];
        const nextItems = headings.map((heading, index) => {
            const label = heading.textContent?.trim() ?? "";
            const base = heading.id || slugify(label) || `onderdeel-${index + 1}`;
            let id = base;
            let duplicate = 2;
            while (used.has(id)) id = `${base}-${duplicate++}`;
            used.add(id);
            heading.id = id;
            return { id, label };
        });

        setItems(nextItems);
        if (!headings.length) return;

        const update = () => {
            let active = headings[0];
            headings.forEach((heading) => {
                if (heading.getBoundingClientRect().top <= 140) active = heading;
            });
            setActiveId(active.id);
        };
        const observer = new IntersectionObserver(update, { rootMargin: "-90px 0px -68% 0px" });
        headings.forEach((heading) => observer.observe(heading));
        window.addEventListener("scroll", update, { passive: true });
        update();
        return () => {
            observer.disconnect();
            window.removeEventListener("scroll", update);
        };
    }, [children]);

    return (
        <div
            className="editorial-article__body dlf-public-page__body dlf-divider-section"
            data-editorial-article
        >
            <nav className="editorial-toc" aria-label="Inhoudsopgave" hidden={!items.length}>
                <span className="editorial-toc__label">{label}</span>
                <ol data-editorial-toc>
                    {items.map((item) => (
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
