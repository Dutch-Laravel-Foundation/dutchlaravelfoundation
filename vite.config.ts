import inertia from "@inertiajs/vite";
import { wayfinder } from "@laravel/vite-plugin-wayfinder";
import react from "@vitejs/plugin-react";
import laravel from "laravel-vite-plugin";
import { defineConfig, loadEnv } from "vite";
import { run } from "vite-plugin-run";

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), "");
    const appUrl = env.VITE_APP_URL ? new URL(env.VITE_APP_URL) : null;

    return {
        plugins: [
            laravel({
                input: [
                    "resources/css/tailwind.css",
                    "resources/js/site.js",
                    "resources/js/app.tsx",
                ],
                ssr: "resources/js/ssr.tsx",
                refresh: true,
                ...(appUrl ? { detectTls: false } : {}),
            }),
            inertia({
                ssr: {
                    host: "127.0.0.1",
                },
            }),
            react(),
            wayfinder({
                command: "php artisan app:wayfinder-generate",
                formVariants: true,
                patterns: ["routes/app.php", "app/Http/Controllers/**/*.php"],
            }),
            run([
                {
                    name: "typescript transform",
                    run: ["php", "artisan", "typescript:transform"],
                    pattern: ["app/{Data,Enums}/**/*.php"],
                },
            ]),
        ],
        server: {
            watch: {
                ignored: ["**/.worktrees/**", "**/vendor/**"],
            },
            ...(appUrl
                ? {
                      hmr: {
                          host: appUrl.hostname,
                          protocol: "wss",
                      },
                  }
                : {}),
        },
    };
});
