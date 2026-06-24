const defaultTheme = require("tailwindcss/defaultTheme");
import preset from "./vendor/filament/support/tailwind.config.preset";

/** @type {import("tailwindcss").Config} */
module.exports = {
    presets: [preset],
    /*
    mode: 'jit', // Enable JIT compilation
    purge: {
      enabled: true, // Enable purging
      content: [
        './resources/views/web/admin/document/download.blade.php',
      ],
    },
    */
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./vendor/laravel/jetstream/**/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./app/Filament/**/*.php",
        "./resources/views/filament/**/*.blade.php",
        "./vendor/filament/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontSize: {
                xxs: ".65rem", // Example of a smaller font size
                xxxs: ".50rem", // Example of a smaller font size
            },
            colors: {
                draft: "#71717a",
                pending: "#fbbf24",
                "waiting-director": "#fbbf24",
                paid: "#10b981",
                active: "#10b981",
                approval: "#fbbf24",
                provisional: "#fbbf24",
                approved: "#1570EF",
                canceled: "#ef4444",
                suspended: "#ef4444",
                admin_brown: "#9D915F",
                admin_blue: "#1b6cb3",
                admin_green: "#10B981",
                // New color palette
                primary: {
                    DEFAULT: "#6482AD",
                    light: "#7FA1C3",
                },
                secondary: {
                    DEFAULT: "#E2DAD6",
                    light: "#F5EDED",
                },
            },
            backgroundColor: {
                action: "#1a56db",
            },
            fontFamily: {
                sans: ["Nunito", ...defaultTheme.fontFamily.sans],
                inter: ["Inter", "sans-serif"],
            },
            backgroundImage: {
                "waves-pattern": "url('../img/layered-waves.svg')",
                "card-waves-blue": "url('/public/img/waves-blue.png')",
                "card-waves-green": "url('/public/img/waves-green.png')",
                "card-waves-yellow": "url('/public/img/waves-yellow.png')",
                "card-waves-grey": "url('/public/img/waves-grey.png')",
                "background-underwater-1":
                    "url('/public/img/background_1.jpg')",
                "wave-blue": "url('/public/img/wave-haikei-blue.svg')",
                "wave-blue-alt": "url('/public/img/wave-haikei-blue-alt.svg')",
                "wave-blue-stacked": "url('/public/img/wave-blue-stacked.svg')",
                "wave-blue-stacked-inverse":
                    "url('/public/img/wave-blue-stacked-inverse.svg')",
                "wave-gray-stacked-light":
                    "url('/public/img/wave-gray-stacked.svg')",
                "wave-gray-stacked":
                    "url('/public/img/wave-haikei-gray-stacked.svg')",
                "wave-gray-right": "url('/public/img/wave-right-gray.svg')",
                "wave-lightblue-bottom":
                    "url('/public/img/wave-bottom-lightblue.svg')",
                "waves-full-bg-one": "url('/public/img/waves-full-bg-one.svg')",
            },
        },
    },

    plugins: [
        require("@tailwindcss/forms"),
        require("@tailwindcss/typography"),
    ],
    safelist: [
        {
            pattern: /(bg|text)-(draft|pending|paid)/,
        },
    ],
    darkMode: "class",
};
