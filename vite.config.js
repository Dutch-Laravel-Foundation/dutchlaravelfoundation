import { defineConfig, loadEnv } from "vite";
import laravel from "laravel-vite-plugin";

export default (mode) => {
    process.env = { ...process.env, ...loadEnv(mode, process.cwd()) };

    const appUrl = process.env.VITE_APP_URL;

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
                  origin: appUrl,
                  hmr: {
                      host: new URL(appUrl).hostname,
                      protocol: "wss",
                      clientPort: 443,
                  },
              }
            : undefined,
    });
};
