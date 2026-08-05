import { createInertiaApp, type ResolvedComponent } from "@inertiajs/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";

import { PersistentSiteLayout } from "@/components/site";

const appName = import.meta.env.VITE_APP_NAME || "Dutch Laravel Foundation";
const pages = import.meta.glob<ResolvedComponent>("./pages/**/*.tsx", { import: "default" });

createInertiaApp({
    layout: () => PersistentSiteLayout,
    progress: {
        color: "#ff2d20",
    },
    resolve: (name) => resolvePageComponent(`./pages/${name}.tsx`, pages),
    title: (title) => title || appName,
    strictMode: true,
});
