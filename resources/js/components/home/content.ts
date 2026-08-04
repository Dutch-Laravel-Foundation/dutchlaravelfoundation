export function plainText(html: string | null, limit?: number): string {
    if (!html) {
        return "";
    }

    const text = html
        .replace(/<[^>]*>/g, " ")
        .replace(/&nbsp;/gi, " ")
        .replace(/&amp;/gi, "&")
        .replace(/&quot;/gi, '"')
        .replace(/&#0*39;|&apos;/gi, "'")
        .replace(/&lt;/gi, "<")
        .replace(/&gt;/gi, ">")
        .replace(/&#(\d+);/g, (_, code: string) => String.fromCodePoint(Number(code)))
        .replace(/\s+/g, " ")
        .trim();

    if (!limit || text.length <= limit) {
        return text;
    }

    return text
        .slice(0, limit)
        .replace(/\s+\S*$/, "")
        .trimEnd();
}

export function contentUrl(
    card: App.Data.Home.ContentCardData,
    collection: "kennis" | "nieuws",
): string {
    return card.url ?? `/${collection}/${card.slug}`;
}
