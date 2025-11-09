import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import { resolve } from 'path';

// Get the main.js where all your JavaScript files are imported
const JS_FILE = resolve('assets/scripts/main.js')
// Define where the compiled and minified JavaScript files will be saved
const BUILD_DIR = resolve(__dirname, 'dist');

export default defineConfig({
    build: {
        assetsDir: '', // Will save the compiled JavaScript files in the root of the dist folder
        manifest: true, // Generate manifest.json file (for caching)
        emptyOutDir: true, // Empty the dist folder before building
        outDir: BUILD_DIR,
        rollupOptions: {
            input: JS_FILE,
        },
    },
    plugins: [
        tailwindcss(),
    ]
});