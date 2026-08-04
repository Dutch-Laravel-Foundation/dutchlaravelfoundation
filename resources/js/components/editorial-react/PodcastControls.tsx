import { Tabs } from "@base-ui/react/tabs";
import { useState } from "react";

import { DlfButtonLink } from "@/components/ui/DlfButton";
import { SmartLink } from "@/components/ui/SmartLink";

import { SpotifyIcon, YouTubeIcon } from "./icons";
import { spotifyEmbedUrl } from "./format";

export const YOUTUBE_CHANNEL = "https://www.youtube.com/@DutchLaravelFoundation";
export const SPOTIFY_CHANNEL = "https://open.spotify.com/show/28cbLx8VKFE0j3xdbRhxsO";

export function PodcastChannelActions() {
    return (
        <>
            <nav className="editorial-channel-links" aria-label="Beluister de DLF podcast">
                <SmartLink href={YOUTUBE_CHANNEL} target="_blank" rel="noopener noreferrer">
                    YouTube <span aria-hidden="true">↗</span>
                </SmartLink>
                <SmartLink href={SPOTIFY_CHANNEL} target="_blank" rel="noopener noreferrer">
                    Spotify <span aria-hidden="true">↗</span>
                </SmartLink>
            </nav>
            <nav className="editorial-channel-actions" aria-label="Beluister de DLF podcast">
                <DlfButtonLink
                    href={YOUTUBE_CHANNEL}
                    target="_blank"
                    rel="noopener noreferrer"
                    face="red"
                    shadow="red"
                >
                    <YouTubeIcon className="dlf-btn-icon dlf-btn-icon--brand" />
                    YouTube
                </DlfButtonLink>
                <SmartLink
                    className="dlf-btn"
                    href={SPOTIFY_CHANNEL}
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <span className="dlf-btn-face dlf-btn-face--spotify">
                        <SpotifyIcon className="dlf-btn-icon dlf-btn-icon--brand" />
                        Spotify
                    </span>
                    <span className="dlf-btn-shadow dlf-btn-shadow--spotify" aria-hidden="true" />
                </SmartLink>
            </nav>
        </>
    );
}

export function PodcastActions({ spotifyUrl, videoUrl }: { spotifyUrl: string; videoUrl: string }) {
    return (
        <div className="editorial-podcast__actions" aria-label="Podcastkanalen">
            {videoUrl ? (
                <DlfButtonLink
                    href={videoUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    face="red"
                    shadow="red"
                >
                    <YouTubeIcon className="dlf-btn-icon dlf-btn-icon--brand" />
                    Bekijk op YouTube
                </DlfButtonLink>
            ) : null}
            {spotifyUrl ? (
                <SmartLink
                    className="dlf-btn"
                    href={spotifyUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <span className="dlf-btn-face dlf-btn-face--spotify">
                        <SpotifyIcon className="dlf-btn-icon dlf-btn-icon--brand" />
                        Luister op Spotify
                    </span>
                    <span className="dlf-btn-shadow dlf-btn-shadow--spotify" aria-hidden="true" />
                </SmartLink>
            ) : null}
        </div>
    );
}

type PodcastTabsProps = {
    descriptionHtml: string;
    spotifyUrl: string;
    title: string;
    transcriptHtml: string;
};

export function PodcastTabs({
    descriptionHtml,
    spotifyUrl,
    title,
    transcriptHtml,
}: PodcastTabsProps) {
    const [activeTab, setActiveTab] = useState<Tabs.Tab.Value>("description");

    return (
        <>
            {spotifyUrl ? (
                <iframe
                    className="editorial-podcast__spotify-embed"
                    title={`Luister naar ${title} op Spotify`}
                    data-consent-src={spotifyEmbedUrl(spotifyUrl)}
                    allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                    loading="lazy"
                    hidden
                />
            ) : null}

            <Tabs.Root value={activeTab} onValueChange={setActiveTab}>
                <Tabs.List className="editorial-podcast__tabs" aria-label="Inhoud aflevering">
                    <Tabs.Tab
                        id="podcast-description-tab"
                        value="description"
                        aria-controls="podcast-description-panel"
                        className={activeTab === "description" ? "is-active" : undefined}
                        onFocus={() => setActiveTab("description")}
                    >
                        Samenvatting
                    </Tabs.Tab>
                    <Tabs.Tab
                        id="podcast-transcript-tab"
                        value="transcript"
                        aria-controls="podcast-transcript-panel"
                        className={activeTab === "transcript" ? "is-active" : undefined}
                        onFocus={() => setActiveTab("transcript")}
                    >
                        Transcript
                    </Tabs.Tab>
                </Tabs.List>
                <section
                    id="podcast-description-panel"
                    role="tabpanel"
                    aria-labelledby="podcast-description-tab"
                    hidden={activeTab !== "description"}
                    data-cms-html
                    dangerouslySetInnerHTML={{ __html: descriptionHtml }}
                />
                <section
                    id="podcast-transcript-panel"
                    role="tabpanel"
                    aria-labelledby="podcast-transcript-tab"
                    hidden={activeTab !== "transcript"}
                    data-cms-html
                    dangerouslySetInnerHTML={{ __html: transcriptHtml }}
                />
            </Tabs.Root>
        </>
    );
}
