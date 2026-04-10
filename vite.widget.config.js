import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
    publicDir: false,
    build: {
        outDir: 'public/build/chatbot',
        emptyOutDir: true,
        lib: {
            entry: resolve(__dirname, 'resources/js/chatbot-widget.js'),
            name: 'EagleChatbot',
            formats: ['iife'],
            fileName: () => 'widget.js',
            cssFileName: 'widget',
        },
        rollupOptions: {
            output: {
                inlineDynamicImports: true,
                assetFileNames: '[name][extname]',
            },
        },
        cssCodeSplit: false,
    },
});
