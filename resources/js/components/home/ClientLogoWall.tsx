import { useEffect, useState } from "react";

import { cn } from "@/lib/utils";

const clientGroups = [
    ["de-verbouwcalculator", "intersafe"],
    ["dropday", "mobiliteitsfabriek"],
    ["eurosafe", "avia"],
    ["abn-amro", "recirculo"],
    ["flow-concepts", "recoll"],
    ["race-planet", "stichting-studiekeuze123"],
    ["youngwize", "nva"],
    ["inventum"],
] as const;

type ClientCellProps = {
    cellIndex: number;
    clients: App.Data.Home.ClientData[];
    tick: number;
};

function ClientCell({ cellIndex, clients, tick }: ClientCellProps) {
    const activeLogoIndex = clients.length
        ? Math.floor((tick + cellIndex) / 6) % clients.length
        : -1;

    return (
        <div className="dlf-home-clients__cell" data-client-cell>
            {clients.map((client, logoIndex) => {
                if (!client.logo) {
                    return null;
                }

                const active = logoIndex === activeLogoIndex;

                return (
                    <div
                        className={cn(
                            "dlf-home-clients__logo",
                            active && "dlf-home-clients__logo--active",
                        )}
                        data-client-logo
                        aria-hidden={active ? undefined : true}
                        key={client.id}
                    >
                        <img
                            src={client.logo.url}
                            alt={client.title}
                            width={client.logo.width ?? undefined}
                            height={client.logo.height ?? undefined}
                            loading="lazy"
                            decoding="async"
                        />
                    </div>
                );
            })}
        </div>
    );
}

export function ClientLogoWall({ clients }: { clients: App.Data.Home.ClientData[] }) {
    const [tick, setTick] = useState(0);
    const clientsBySlug = new Map(clients.map((client) => [client.slug, client]));

    useEffect(() => {
        const interval = window.setInterval(() => setTick((current) => current + 1), 2500);

        return () => window.clearInterval(interval);
    }, []);

    return (
        <section
            className="dlf-home-clients dlf-divider-section"
            aria-labelledby="clients-heading"
            data-client-logo-wall
        >
            <div className="dlf-home-clients__lanes">
                <div className="dlf-home-clients__grid">
                    <div
                        className="dlf-home-clients__cell dlf-home-clients__cell--empty"
                        data-client-cell
                        aria-hidden="true"
                    />
                    <div
                        className="dlf-home-clients__cell dlf-home-clients__cell--empty"
                        data-client-cell
                        aria-hidden="true"
                    />
                    {clientGroups.slice(0, 4).map((slugs, index) => (
                        <ClientCell
                            cellIndex={index + 2}
                            clients={slugs.flatMap((slug) => {
                                const client = clientsBySlug.get(slug);

                                return client ? [client] : [];
                            })}
                            tick={tick}
                            key={slugs.join("|")}
                        />
                    ))}
                    <div
                        className="dlf-home-clients__cell dlf-home-clients__cell--empty"
                        data-client-cell
                        aria-hidden="true"
                    />
                    <div
                        className="dlf-home-clients__cell dlf-home-clients__cell--empty"
                        data-client-cell
                        aria-hidden="true"
                    />
                    {clientGroups.slice(4).map((slugs, index) => (
                        <ClientCell
                            cellIndex={index + 8}
                            clients={slugs.flatMap((slug) => {
                                const client = clientsBySlug.get(slug);

                                return client ? [client] : [];
                            })}
                            tick={tick}
                            key={slugs.join("|")}
                        />
                    ))}
                </div>
                <div className="dlf-home-clients__fade" aria-hidden="true" />
            </div>
            <header className="dlf-home-clients__label">
                <h2 id="clients-heading">
                    Klanten van onze leden die vertrouwen op het Laravel framework
                </h2>
            </header>
        </section>
    );
}
