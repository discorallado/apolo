import { defineConfig } from 'vite'
import laravel, { refreshPaths } from 'laravel-vite-plugin'
// import vue from 'vue';

export default defineConfig({
    plugins: [
        // vue(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/filament/admin/theme.css',
            ],
            refresh: [
                ...refreshPaths,
                'app/Http/Livewire/**',
                'app/Forms/Components/**',
                'app/Tables/Columns/**',
            ],
        }),
    ],
    // alias: {
    //     'vue': 'vue/dist/vue.esm-bundler.js'
    //     // O la ruta correcta según tu configuración
    // }
});
