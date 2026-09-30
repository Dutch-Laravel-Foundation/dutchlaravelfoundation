import { Link, type InertiaLinkProps, router } from "@inertiajs/react";
import { type ComponentPropsWithoutRef, useEffect } from "react";

type SmartLinkProps = ComponentPropsWithoutRef<"a"> & {
    onPrefetched?: InertiaLinkProps["onPrefetched"];
    reset?: string[];
    viewTransition?: InertiaLinkProps["viewTransition"];
};

type CmsAnchor = Pick<HTMLAnchorElement, "download" | "hasAttribute" | "href" | "target">;

type CmsLinkEvent = Pick<
    MouseEvent,
    | "altKey"
    | "button"
    | "ctrlKey"
    | "defaultPrevented"
    | "metaKey"
    | "preventDefault"
    | "shiftKey"
    | "target"
>;

type CmsLinkHandlerOptions = {
    findAnchor: (event: CmsLinkEvent) => CmsAnchor | null;
    origin: string;
    prefetch: (href: string, visitOptions: object, prefetchOptions: { cacheFor: string }) => void;
    visit: (href: string) => void;
};

const assetExtension =
    /\.(?:avif|bmp|csv|docx?|eot|gif|ico|jpe?g|js|json|m4a|mov|mp3|mp4|mpeg|ogg|ogv|pdf|png|pptx?|rar|svg|tar|tgz|tiff?|ttf|txt|wav|webm|webp|woff2?|xlsx?|xml|zip)$/i;

function isExplicitDownload(download: SmartLinkProps["download"]) {
    return download !== undefined && download !== null && download !== false;
}

function hasNonHttpScheme(href: string) {
    return /^[a-z][a-z\d+.-]*:/i.test(href) && !/^https?:/i.test(href);
}

function isControlPanelPath(pathname: string) {
    return (
        pathname === "/cp" || pathname.startsWith("/cp/") || pathname.startsWith("/index.php/cp")
    );
}

export function shouldUseInertiaLink(
    { download, href, target }: SmartLinkProps,
    currentOrigin = typeof window === "undefined" ? undefined : window.location?.origin,
) {
    const destination = href?.trim();

    if (!destination || destination.startsWith("#") || hasNonHttpScheme(destination)) {
        return false;
    }

    if (target && target.toLowerCase() !== "_self") {
        return false;
    }

    if (isExplicitDownload(download)) {
        return false;
    }

    if (/^https?:/i.test(destination) || destination.startsWith("//")) {
        if (!currentOrigin || new URL(destination, currentOrigin).origin !== currentOrigin) {
            return false;
        }
    }

    const url = new URL(destination, currentOrigin ?? "https://inertia.local");

    return !isControlPanelPath(url.pathname) && !assetExtension.test(url.pathname);
}

function isModifiedClick(event: CmsLinkEvent) {
    return event.altKey || event.ctrlKey || event.metaKey || event.shiftKey;
}

function isEligibleCmsAnchor(anchor: CmsAnchor, origin: string) {
    return shouldUseInertiaLink(
        {
            download: anchor.hasAttribute("download") ? anchor.download || true : undefined,
            href: anchor.href,
            target: anchor.target || undefined,
        },
        origin,
    );
}

export function createCmsLinkHandlers({
    findAnchor,
    origin,
    prefetch,
    visit,
}: CmsLinkHandlerOptions) {
    function prefetchAnchor(event: CmsLinkEvent) {
        const anchor = findAnchor(event);

        if (!anchor || !isEligibleCmsAnchor(anchor, origin)) {
            return;
        }

        prefetch(anchor.href, {}, { cacheFor: "30s" });
    }

    return {
        click(event: CmsLinkEvent) {
            if (event.defaultPrevented || event.button !== 0 || isModifiedClick(event)) {
                return;
            }

            const anchor = findAnchor(event);

            if (!anchor || !isEligibleCmsAnchor(anchor, origin)) {
                return;
            }

            event.preventDefault();
            visit(anchor.href);
        },
        mousedown(event: CmsLinkEvent) {
            if (event.button === 0) {
                prefetchAnchor(event);
            }
        },
        mouseover: prefetchAnchor,
    };
}

function findCmsAnchor(event: CmsLinkEvent): HTMLAnchorElement | null {
    if (!(event.target instanceof Element)) {
        return null;
    }

    const anchor = event.target.closest("a[href]");

    if (!(anchor instanceof HTMLAnchorElement) || !anchor.closest("[data-cms-html]")) {
        return null;
    }

    return anchor;
}

export function SmartLinkEnhancer() {
    useEffect(() => {
        const handlers = createCmsLinkHandlers({
            findAnchor: findCmsAnchor,
            origin: window.location.origin,
            prefetch: (href, visitOptions, prefetchOptions) =>
                router.prefetch(href, visitOptions, prefetchOptions),
            visit: (href) => router.visit(href),
        });
        const click = (event: MouseEvent) => handlers.click(event);
        const mousedown = (event: MouseEvent) => handlers.mousedown(event);
        const mouseover = (event: MouseEvent) => handlers.mouseover(event);

        document.addEventListener("click", click);
        document.addEventListener("mousedown", mousedown);
        document.addEventListener("mouseover", mouseover);

        return () => {
            document.removeEventListener("click", click);
            document.removeEventListener("mousedown", mousedown);
            document.removeEventListener("mouseover", mouseover);
        };
    }, []);

    return null;
}

export function SmartLink({ href, onPrefetched, reset, viewTransition, ...props }: SmartLinkProps) {
    if (shouldUseInertiaLink({ href, ...props })) {
        const { onClick, onFocus, onMouseDown, onMouseEnter, ...anchorProps } = props;
        const inertiaProps = anchorProps as InertiaLinkProps;
        const prefetchResetLink = () => {
            if (!reset?.length) {
                return;
            }

            router.prefetch(
                href!,
                {
                    onPrefetched,
                    reset,
                },
                { cacheFor: "30s" },
            );
        };
        const inertiaOnClick: InertiaLinkProps["onClick"] = reset?.length
            ? (event) => {
                  onClick?.(event as React.MouseEvent<HTMLAnchorElement>);

                  const target = event.currentTarget as HTMLAnchorElement;
                  const eventTarget = event.target as HTMLElement | null;

                  if (
                      event.defaultPrevented ||
                      eventTarget?.isContentEditable ||
                      event.altKey ||
                      event.ctrlKey ||
                      event.metaKey ||
                      event.shiftKey ||
                      event.button !== 0 ||
                      (target.target && target.target !== "_self")
                  ) {
                      return;
                  }

                  event.preventDefault();
                  router.visit(href!, viewTransition ? { reset, viewTransition } : { reset });
              }
            : (onClick as InertiaLinkProps["onClick"]);

        return (
            <Link
                {...inertiaProps}
                href={href!}
                prefetch={reset?.length ? undefined : "hover"}
                cacheFor="30s"
                viewTransition={viewTransition}
                onFocus={
                    reset?.length
                        ? (event) => {
                              onFocus?.(event as React.FocusEvent<HTMLAnchorElement>);

                              if (!event.defaultPrevented) {
                                  prefetchResetLink();
                              }
                          }
                        : onFocus
                }
                onMouseDown={
                    reset?.length
                        ? (event) => {
                              onMouseDown?.(event as React.MouseEvent<HTMLAnchorElement>);

                              if (!event.defaultPrevented && event.button === 0) {
                                  prefetchResetLink();
                              }
                          }
                        : onMouseDown
                }
                onMouseEnter={
                    reset?.length
                        ? (event) => {
                              onMouseEnter?.(event as React.MouseEvent<HTMLAnchorElement>);

                              if (!event.defaultPrevented) {
                                  prefetchResetLink();
                              }
                          }
                        : onMouseEnter
                }
                onPrefetched={reset?.length ? undefined : onPrefetched}
                onClick={inertiaOnClick}
            />
        );
    }

    return <a {...props} href={href} />;
}
