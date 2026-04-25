import { defineConfig, loadEnv } from "vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import path from "path";
import fs from "fs";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

/**
 * Copies TinyMCE static assets (skins, themes, icons, models, plugins)
 * from node_modules into the public build directory so they can be
 * resolved at runtime without relative-path issues.
 */
function copyTinymceAssets() {
    const tinymceSrc = path.resolve(__dirname, "node_modules/tinymce");
    const tinymceDest = path.resolve(__dirname, "../../../public/admin/build/tinymce");

    const dirs = ["skins", "themes", "icons", "models", "plugins"];

    return {
        name: "copy-tinymce-assets",
        writeBundle() {
            if (!fs.existsSync(tinymceDest)) {
                fs.mkdirSync(tinymceDest, { recursive: true });
            }

            // Copy tinymce core file
            const coreFile = path.join(tinymceSrc, "tinymce.min.js");
            if (fs.existsSync(coreFile)) {
                fs.copyFileSync(coreFile, path.join(tinymceDest, "tinymce.min.js"));
            }

            // Copy asset directories
            dirs.forEach((dir) => {
                const src = path.join(tinymceSrc, dir);
                const dest = path.join(tinymceDest, dir);

                if (fs.existsSync(src)) {
                    fs.cpSync(src, dest, { recursive: true });
                }
            });
        },
    };
}

export default defineConfig(({ mode }) => {
    const envDir = "../../../";

    Object.assign(process.env, loadEnv(mode, envDir));

    return {
        build: {
            emptyOutDir: true,
        },

        envDir,

        server: {
            host: process.env.VITE_HOST || "localhost",
            port: process.env.VITE_PORT || 5173,
            cors: true,
        },

        plugins: [
            vue(),

            copyTinymceAssets(),

            laravel({
                hotFile: "../../../public/admin-vite.hot",
                publicDirectory: "../../../public",
                buildDirectory: "admin/build",
                input: [
                    "src/Resources/assets/css/app.css",
                    "src/Resources/assets/js/app.js",
                    "src/Resources/assets/js/chart.js",
                ],
                refresh: true,
            }),
        ],

        experimental: {
            renderBuiltUrl(filename, { hostId, hostType, type }) {
                if (hostType === "css") {
                    return path.basename(filename);
                }
            },
        },
    };
});
