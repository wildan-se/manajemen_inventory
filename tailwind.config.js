import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Inter", "Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                indigo: {
                    950: "#1e1b4b",
                },
            },
            backgroundImage: {
                "sidebar-gradient":
                    "linear-gradient(180deg, #1e1b4b 0%, #0f172a 100%)",
            },
        },
    },

    plugins: [forms],
};
