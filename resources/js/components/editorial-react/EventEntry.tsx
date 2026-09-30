import { SmartLink } from "@/components/ui/SmartLink";

import { EditorialMeta } from "./EditorialMeta";
import { EditorialImage } from "./Media";
import { truncate } from "./format";

type EventEntryProps = {
    event: App.Data.Editorial.EventCardData;
    featured?: boolean;
    index: number;
    past?: boolean;
};

export function EventEntry({ event, featured = false, index, past = false }: EventEntryProps) {
    const href = event.url ?? event.uri ?? `/events/${event.slug}`;

    return (
        <article
            className={`editorial-entry ${index % 2 === 0 ? "editorial-entry--media-start" : "editorial-entry--media-end"}${featured ? " editorial-entry--featured" : ""}${past ? " editorial-entry--past" : ""}`}
        >
            <SmartLink
                className="editorial-entry__media"
                href={href}
                aria-label={`Bekijk ${event.title}`}
                data-progressive-media-frame
            >
                <EditorialImage
                    asset={event.featuredImage}
                    className={`editorial-entry__image${past ? " grayscale" : ""}`}
                    title={event.title}
                />
            </SmartLink>

            <div className="editorial-entry__body">
                <EditorialMeta category={event.type} date={event.dateStart} />
                <h2
                    className={`editorial-entry__title${featured ? " editorial-entry__title--featured" : ""}`}
                >
                    <SmartLink href={href}>{event.title}</SmartLink>
                </h2>
                {event.introduction ? (
                    <p className="editorial-entry__summary">{truncate(event.introduction)}</p>
                ) : null}
                <SmartLink className="editorial-text-link" href={href}>
                    Bekijk evenement <span aria-hidden="true">→</span>
                </SmartLink>
            </div>
        </article>
    );
}
