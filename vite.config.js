import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    /*
    server: {
        https: {
            key: fs.readFileSync("localhost-key.pem"),
            cert: fs.readFileSync("localhost.pem"),
        },
        host: "localhost",
        port: 3000,
        hmr: {
            host: "localhost",
        },
    },
    */
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/css/custom-styles.css",
                "resources/js/app.js",
                "resources/js/qr-code-scanner.js",
            ],
            refresh: true,
        }),
    ],
});
