import { DlfButtonLink } from "@/components/ui/DlfButton";

import { formatDate, machineDate } from "./format";

function Fact({ children, label }: { children: React.ReactNode; label: string }) {
    return (
        <div className="grid grid-cols-1 gap-1 border-b border-[#ececec] px-6 py-4 sm:grid-cols-[10rem_1fr] sm:gap-6">
            <dt className="font-semibold text-[#090910]">{label}</dt>
            <dd className="m-0!">{children}</dd>
        </div>
    );
}

export function EventFacts({ event }: { event: App.Data.Editorial.EventData }) {
    return (
        <section
            className="editorial-event-facts mt-12 overflow-hidden rounded-[3px] border border-[#ececec]"
            aria-labelledby="event-facts-heading"
        >
            <header className="border-b border-[#ececec] bg-white px-6 py-5">
                <span className="editorial-eyebrow">Evenement</span>
                <h2
                    id="event-facts-heading"
                    className="mt-0! mb-0! text-xl! font-semibold! leading-tight! text-[#090910]!"
                >
                    Praktische informatie
                </h2>
            </header>

            <dl className="m-0!">
                {event.type ? <Fact label="Soort event">{event.type}</Fact> : null}
                {event.dateStart ? (
                    <Fact label="Datum">
                        <time className="capitalize" dateTime={machineDate(event.dateStart)}>
                            {formatDate(event.dateStart, true)}
                        </time>
                    </Fact>
                ) : null}
                {event.timeStart ? (
                    <Fact label="Tijd">
                        <time dateTime={event.timeStart}>{event.timeStart}</time>
                        {event.timeEnd ? (
                            <>
                                {" "}
                                <span aria-hidden="true">–</span>{" "}
                                <time dateTime={event.timeEnd}>{event.timeEnd}</time>
                            </>
                        ) : null}
                    </Fact>
                ) : null}
                {event.location ? <Fact label="Locatie">{event.location}</Fact> : null}
                {event.address ? <Fact label="Adres">{event.address}</Fact> : null}
            </dl>

            {event.signupLink ? (
                <div className="border-t border-[#ececec] bg-white px-6 py-6">
                    <DlfButtonLink
                        href={event.signupLink}
                        target="_blank"
                        rel="noopener noreferrer"
                        face="red"
                        shadow="red"
                    >
                        Inschrijven
                    </DlfButtonLink>
                </div>
            ) : null}
        </section>
    );
}
