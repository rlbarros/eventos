import { defineConfig } from "vite";
import laravel, { refreshPaths } from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
  plugins: [
    laravel({
      input: ["resources/css/app.css", "resources/js/app.js"],
      refresh: [
        ...refreshPaths,
        "app/Http/Livewire/**", // Example for Livewire files
        "app/Filament/**", // Example for Filament files
        // Add any other paths you want to watch
      ],
    }),
    tailwindcss(),
  ],
  server: {
    cors: true,
    watch: {
      ignored: ["**/storage/framework/views/**"],
    },
  },
});
