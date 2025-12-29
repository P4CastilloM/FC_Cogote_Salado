import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  base: '/fccogotesalado/',
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js',

        // ✅ tus archivos de esta página
        'resources/css/public/home.css',
        'resources/js/public/home.js',

        // ✅ tus archivos de fotos
        'resources/css/public/fotos.css',
        'resources/js/public/fotos.js',

        // ✅ tus archivos de navbar
        'resources/css/public/navbar.css',

      ],
      refresh: true,
    }),
  ],
});
