import { formatDate, machineDate } from "./format";

type EditorialMetaProps = {
    category?: string | null;
    date?: string | null;
    article?: boolean;
};

export function EditorialMeta({ article = false, category, date }: EditorialMetaProps) {
    return (
        <div className={`editorial-entry__meta${article ? " editorial-article__meta" : ""}`}>
            {category ? <span className="editorial-entry__category">{category}</span> : null}
            {category && date ? (
                <span className="editorial-entry__dot" aria-hidden="true">
                    ·
                </span>
            ) : null}
            {date ? <time dateTime={machineDate(date)}>{formatDate(date)}</time> : null}
        </div>
    );
}
