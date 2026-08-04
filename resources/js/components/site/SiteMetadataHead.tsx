import { Head, usePage } from "@inertiajs/react";

type SeoMetadata = {
    canonicalUrl: string;
    description: string;
    jsonLd: string;
    keywords: string | null;
    openGraphType: string;
    socialImageUrl: string;
    title: string;
};

type SharedAppProps = {
    app: {
        cspNonce: string;
        seo: SeoMetadata;
    };
};

export function SiteMetadataHead() {
    const { cspNonce, seo } = usePage<SharedAppProps>().props.app;

    return (
        <Head title={seo.title}>
            <meta head-key="description" name="description" content={seo.description} />
            {seo.keywords ? (
                <meta head-key="keywords" name="keywords" content={seo.keywords} />
            ) : null}
            <link head-key="canonical" rel="canonical" href={seo.canonicalUrl} />
            <meta head-key="og-title" property="og:title" content={seo.title} />
            <meta head-key="og-type" property="og:type" content={seo.openGraphType} />
            <meta head-key="og-url" property="og:url" content={seo.canonicalUrl} />
            <meta head-key="og-description" property="og:description" content={seo.description} />
            <meta head-key="og-image" property="og:image" content={seo.socialImageUrl} />
            <meta head-key="twitter-card" name="twitter:card" content="summary_large_image" />
            <meta head-key="twitter-title" name="twitter:title" content={seo.title} />
            <meta
                head-key="twitter-description"
                name="twitter:description"
                content={seo.description}
            />
            <meta head-key="twitter-image" name="twitter:image" content={seo.socialImageUrl} />
            <script
                head-key="structured-data"
                type="application/ld+json"
                nonce={cspNonce}
                dangerouslySetInnerHTML={{ __html: seo.jsonLd }}
            />
            <meta head-key="ms-tile-color" name="msapplication-TileColor" content="#ffffff" />
            <meta head-key="theme-color" name="theme-color" content="#ffffff" />
            <link
                head-key="apple-touch-icon"
                rel="apple-touch-icon"
                sizes="180x180"
                href="/apple-touch-icon.png"
            />
            <link
                head-key="favicon-32"
                rel="icon"
                type="image/png"
                sizes="32x32"
                href="/favicon-32x32.png"
            />
            <link
                head-key="favicon-16"
                rel="icon"
                type="image/png"
                sizes="16x16"
                href="/favicon-16x16.png"
            />
            <link head-key="manifest" rel="manifest" href="/site.webmanifest" />
            <link
                head-key="mask-icon"
                rel="mask-icon"
                href="/safari-pinned-tab.svg"
                color="#5bbad5"
            />
        </Head>
    );
}
