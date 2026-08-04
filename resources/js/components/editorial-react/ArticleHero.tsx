import { SmartLink } from "@/components/ui/SmartLink";

import { EditorialMeta } from "./EditorialMeta";
import { EditorialImage } from "./Media";

type ArticleHeroProps = {
    author?: App.Data.Editorial.AuthorData | null;
    authors?: readonly App.Data.Editorial.AuthorData[];
    category?: string | null;
    date?: string | null;
    featuredImage: App.Data.Editorial.AssetData | null;
    introduction?: string | null;
    title: string;
    children?: React.ReactNode;
};

function AuthorImage({ author }: { author: App.Data.Editorial.AuthorData }) {
    const source = author.image?.url ?? author.image?.permalink ?? author.imageUrl;

    if (!source) {
        return null;
    }

    return (
        <img
            src={source}
            alt={author.image?.alt ?? author.name}
            width={author.image?.width ?? undefined}
            height={author.image?.height ?? undefined}
            loading="lazy"
            decoding="async"
        />
    );
}

export function ArticleHero({
    author,
    authors = [],
    category,
    children,
    date,
    featuredImage,
    introduction,
    title,
}: ArticleHeroProps) {
    return (
        <header className="editorial-article__hero dlf-divider-section dlf-divider-split dlf-divider-split--stacked-reversed">
            <div className="editorial-article__head">
                <EditorialMeta article category={category} date={date} />
                <h1>{title}</h1>
                {introduction ? (
                    <div
                        className="editorial-article__lead"
                        data-cms-html
                        dangerouslySetInnerHTML={{ __html: introduction }}
                    />
                ) : null}

                {author ? (
                    <div className="editorial-article__author-summary">
                        <AuthorImage author={author} />
                        <span>
                            <strong>{author.name}</strong>
                            {author.role ? <small>{author.role}</small> : null}
                        </span>
                    </div>
                ) : null}

                {authors.length ? (
                    <div className="editorial-article__author-summary" aria-label="Auteur(s)">
                        {authors.map((item, index) => (
                            <SmartLink
                                className="editorial-article__author-summary-item"
                                href="#editorial-authors"
                                key={item.id ?? `${item.name}-${index}`}
                            >
                                <AuthorImage author={item} />
                                <span>
                                    <strong>{item.name}</strong>
                                    {item.role ? <small>{item.role}</small> : null}
                                </span>
                            </SmartLink>
                        ))}
                    </div>
                ) : null}

                {children}
            </div>

            <figure className="editorial-article__figure" data-progressive-media-frame>
                <EditorialImage asset={featuredImage} eager title={title} />
            </figure>
        </header>
    );
}
