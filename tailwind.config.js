import { defineConfig } from "@tailwindcss/vite";

export default defineConfig({
  content: [
    "./resources/views/**/*.blade.php",
    "./resources/js/**/*.js",
    "./resources/**/*.vue",
    "./vendor/livewire/flux/**/*.blade.php",
  ],
  theme: {
    extend: {
      colors: {
        primary: "#047857",
        secondary: "#1f2937",
        accent: "#06b6d4",
      },
    },
  },
});
