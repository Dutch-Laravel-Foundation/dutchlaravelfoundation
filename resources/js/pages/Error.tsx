import { Head } from "@inertiajs/react";

import { DlfButtonLink } from "@/components/ui/DlfButton";

type Props = {
    error: { status: number };
    site: App.Data.SiteShell.SiteShellData;
};

const messages: Record<number, { description: string; title: string }> = {
    403: {
        title: "Geen toegang",
        description: "Je hebt geen toegang tot deze pagina.",
    },
    404: {
        title: "Pagina niet gevonden",
        description: "Sorry, we kunnen de pagina die je zoekt niet vinden.",
    },
    500: {
        title: "Er ging iets mis",
        description: "Er ging iets mis bij het laden van deze pagina.",
    },
    503: {
        title: "Tijdelijk niet beschikbaar",
        description: "De website is tijdelijk niet beschikbaar. Probeer het later opnieuw.",
    },
};

export default function ErrorPage({ error }: Props) {
    const message = messages[error.status] ?? messages[500];

    return (
        <>
            <Head title={message.title}>
                <meta head-key="robots" name="robots" content="noindex, nofollow" />
            </Head>

            <div className="flex min-h-[60vh] items-center py-20">
                <div className="container mx-auto px-6 lg:px-10">
                    <div className="flex flex-col items-center justify-between gap-12 xl:flex-row xl:gap-16">
                        {error.status === 404 ? (
                            <div className="flex flex-1 justify-center xl:flex-initial">
                                <img
                                    src="/assets/img/404.svg"
                                    width="431"
                                    height="269"
                                    className="w-full max-w-md"
                                    alt="404 - Pagina niet gevonden"
                                />
                            </div>
                        ) : null}

                        <div className="w-full flex-1 sm:w-3/4 lg:w-1/2">
                            <span className="editorial-eyebrow">Fout {error.status}</span>
                            <h1 className="mb-8">{message.title}</h1>
                            <p className="mb-8">{message.description}</p>
                            <DlfButtonLink href="/" face="red" shadow="red">
                                Ga naar de homepagina
                            </DlfButtonLink>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
