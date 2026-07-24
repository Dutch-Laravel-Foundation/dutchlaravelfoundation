import { defineConfig, loadEnv } from "vite";
import laravel from "laravel-vite-plugin";

export default (mode) => {
    process.env = { ...process.env, ...loadEnv(mode, process.cwd()) };
    const appUrl = process.env.VITE_APP_URL
        ? new URL(process.env.VITE_APP_URL)
        : null;

    return defineConfig({
        plugins: [
            laravel({
                input: ["resources/css/tailwind.css", "resources/js/site.js"],
                refresh: true,
                ...(appUrl ? { detectTls: false } : {}),
            }),
        ],
        server: appUrl
            ? {
                  hmr: {
                      host: appUrl.hostname,
                      protocol: "wss",
                  },
              }
            : {},
    });
};
