import { defineConfig, loadEnv } from "vite";
import laravel from "laravel-vite-plugin";

export default (mode) => {
    process.env = { ...process.env, ...loadEnv(mode, process.cwd()) };

    return defineConfig({
        plugins: [
            laravel({
                input: ["resources/css/tailwind.css", "resources/js/site.js"],
                detectTls: process.env.VITE_VALET_HOST,
                refresh: true,
            }),
        ],
        refresh: true,
    });
};
