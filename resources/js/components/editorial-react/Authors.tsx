import { DlfButtonLink } from "@/components/ui/DlfButton";
import { SmartLink } from "@/components/ui/SmartLink";

function authorImageSource(author: App.Data.Editorial.AuthorData): string | null {
    return author.image?.url ?? author.image?.permalink ?? author.imageUrl;
}

function AuthorPortrait({ author }: { author: App.Data.Editorial.AuthorData }) {
    const source = authorImageSource(author);

    if (!source) {
        return null;
    }

    return (
        <div className="editorial-author__portrait">
            <img
                src={source}
                alt={author.image?.alt ?? author.name}
                width={author.image?.width ?? undefined}
                height={author.image?.height ?? undefined}
                loading="lazy"
                decoding="async"
            />
        </div>
    );
}

function WebsiteIcon() {
    return (
        <svg className="editorial-author__icon" viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" strokeWidth="1.8" />
            <path
                d="M3.5 12h17M12 3c2.2 2.5 3.4 5.5 3.4 9S14.2 18.5 12 21M12 3C9.8 5.5 8.6 8.5 8.6 12S9.8 18.5 12 21"
                fill="none"
                stroke="currentColor"
                strokeWidth="1.8"
                strokeLinecap="round"
            />
        </svg>
    );
}

export function InsightAuthor({ author }: { author: App.Data.Editorial.AuthorData | null }) {
    if (!author) {
        return null;
    }

    return (
        <section
            className="editorial-author dlf-divider-section"
            id="editorial-author"
            data-editorial-author
        >
            <span className="editorial-author__label">Over de auteur</span>
            <div className="editorial-author__grid">
                <div className="editorial-author__content">
                    <AuthorPortrait author={author} />

                    <div className="editorial-author__details">
                        <h2>{author.name}</h2>
                        {author.role ? (
                            <p className="editorial-author__role">{author.role}</p>
                        ) : null}
                        {author.bio ? (
                            <div
                                className="editorial-author__bio"
                                data-cms-html
                                dangerouslySetInnerHTML={{ __html: author.bio }}
                            />
                        ) : null}
                        {author.profileUrl ? (
                            <DlfButtonLink
                                className="editorial-author__link"
                                href={author.profileUrl}
                                target="_blank"
                                rel="noopener noreferrer"
                                face="red"
                                shadow="red"
                            >
                                Meer over {author.name}
                            </DlfButtonLink>
                        ) : null}
                    </div>
                </div>
            </div>
        </section>
    );
}

export function KnowledgeAuthors({
    authors,
}: {
    authors: readonly App.Data.Editorial.AuthorData[];
}) {
    if (!authors.length) {
        return null;
    }

    return (
        <section
            className="editorial-author editorial-author--knowledge dlf-divider-section"
            id="editorial-authors"
            data-knowledge-authors
        >
            <span className="editorial-author__label">
                {authors.length > 1 ? "Over de auteurs" : "Over de auteur"}
            </span>

            <div
                className={`editorial-author__list${authors.length > 1 ? " editorial-author__list--multiple" : ""}`}
            >
                {authors.map((author, index) => (
                    <article
                        className="editorial-author__grid"
                        key={author.id ?? `${author.name}-${index}`}
                    >
                        <div className="editorial-author__content">
                            <AuthorPortrait author={author} />

                            <div className="editorial-author__details">
                                <h2>{author.name}</h2>
                                {author.role ? (
                                    <p className="editorial-author__role">{author.role}</p>
                                ) : null}
                                {author.bio ? (
                                    <div
                                        className="editorial-author__bio"
                                        data-cms-html
                                        dangerouslySetInnerHTML={{ __html: author.bio }}
                                    />
                                ) : null}

                                {author.linkedinUrl || author.websiteUrl ? (
                                    <div className="editorial-author__actions">
                                        {author.linkedinUrl ? (
                                            <SmartLink
                                                className="editorial-author__link"
                                                href={author.linkedinUrl}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                aria-label={`LinkedIn van ${author.name}`}
                                            >
                                                <span className="editorial-author__link-face">
                                                    <img
                                                        className="editorial-author__icon"
                                                        src="/assets/redesign/socials/linkedin.svg"
                                                        width="24"
                                                        height="24"
                                                        alt=""
                                                    />
                                                </span>
                                            </SmartLink>
                                        ) : null}
                                        {author.websiteUrl ? (
                                            <SmartLink
                                                className="editorial-author__link"
                                                href={author.websiteUrl}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                aria-label={`Website van ${author.name}`}
                                            >
                                                <span className="editorial-author__link-face">
                                                    <WebsiteIcon />
                                                </span>
                                            </SmartLink>
                                        ) : null}
                                    </div>
                                ) : null}
                            </div>
                        </div>
                    </article>
                ))}
            </div>
        </section>
    );
}
