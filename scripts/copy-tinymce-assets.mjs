import { cpSync, existsSync, mkdirSync, rmSync } from "node:fs";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";

const root = dirname(dirname(fileURLToPath(import.meta.url)));
const source = join(root, "node_modules", "tinymce");
const target = join(root, "public", "vendor", "tinymce");

if (!existsSync(source)) {
    console.error("TinyMCE assets are missing. Run `npm ci` before building frontend assets.");
    process.exit(1);
}

rmSync(target, { recursive: true, force: true });
mkdirSync(target, { recursive: true });

for (const path of ["icons", "models", "plugins", "skins", "themes"]) {
    cpSync(join(source, path), join(target, path), { recursive: true });
}

for (const file of ["license.txt", "tinymce.min.js"]) {
    if (existsSync(join(source, file))) {
        cpSync(join(source, file), join(target, file));
    }
}
