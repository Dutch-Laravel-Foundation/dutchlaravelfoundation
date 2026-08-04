export function plainText(value: string | null | undefined): string {
    if (!value) {
        return "";
    }

    return value
        .replace(/<[^>]*>/g, " ")
        .replace(/&nbsp;/g, " ")
        .replace(/&amp;/g, "&")
        .replace(/&quot;/g, '"')
        .replace(/&#039;|&apos;/g, "'")
        .replace(/\s+/g, " ")
        .trim();
}

export function truncate(value: string | null | undefined, length = 240): string {
    const text = plainText(value);

    if (text.length <= length) {
        return text;
    }

    return `${text
        .slice(0, length)
        .replace(/\s+\S*$/, "")
        .trimEnd()}...`;
}

function parseDate(value: string): Date | null {
    const normalized = /^\d{4}-\d{2}-\d{2} /.test(value)
        ? value.replace(" ", "T")
        : value.replace(/(\d+)(st|nd|rd|th)/i, "$1");
    const parsed = new Date(normalized);

    return Number.isNaN(parsed.getTime()) ? null : parsed;
}

export function machineDate(value: string | null | undefined): string | undefined {
    if (!value) {
        return undefined;
    }

    const date = parseDate(value);

    if (!date) {
        return undefined;
    }

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
}

export function formatDate(value: string | null | undefined, includeWeekday = false): string {
    if (!value) {
        return "";
    }

    const date = parseDate(value);

    if (!date) {
        return value;
    }

    return new Intl.DateTimeFormat("nl-NL", {
        day: "numeric",
        month: "long",
        weekday: includeWeekday ? "long" : undefined,
        year: "numeric",
    }).format(date);
}

export function pageUrl(base: string, page: number, category?: string | null): string {
    const parameters = new URLSearchParams();

    if (category) {
        parameters.set("category", category);
    }

    if (page > 1) {
        parameters.set("page", String(page));
    }

    const query = parameters.toString();

    return query ? `${base}?${query}` : base;
}

export function slugify(value: string): string {
    return value
        .toLocaleLowerCase("nl")
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-|-$/g, "");
}

export function embedVideoUrl(value: string): string | null {
    try {
        const url = new URL(value);

        if (url.hostname === "youtu.be") {
            return `https://www.youtube-nocookie.com/embed/${url.pathname.slice(1)}`;
        }

        if (url.hostname.includes("youtube.com")) {
            const id = url.searchParams.get("v") ?? url.pathname.split("/").filter(Boolean).at(-1);

            return id ? `https://www.youtube-nocookie.com/embed/${id}` : null;
        }

        if (url.hostname === "vimeo.com" || url.hostname.endsWith(".vimeo.com")) {
            const id = url.pathname.split("/").filter(Boolean).at(-1);

            return id ? `https://player.vimeo.com/video/${id}` : null;
        }
    } catch {
        return null;
    }

    return null;
}

export function spotifyEmbedUrl(value: string): string {
    return value.replace("open.spotify.com/", "open.spotify.com/embed/");
}
