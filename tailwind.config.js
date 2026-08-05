module.exports = {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.{js,jsx,ts,tsx}",
        "./content/**/*.md",
    ],
    corePlugins: {
        container: false,
    },
    important: false,
    theme: {
        screens: {
            sm: "640px",
            md: "768px",
            base: "835px",
            lg: "1024px",
            xl: "1200px",
            "2xl": "1400px",
            "3xl": "1600px",
            "4xl": "2000px",
        },
        fontFamily: {
            sans: ["Mulish Variable", "sans-serif"],
        },
        fontSize: {
            xs: [
                "12px",
                {
                    lineHeight: "2em",
                },
            ],
            sm: [
                "14px",
                {
                    lineHeight: "1.714em",
                },
            ],
            base: [
                "16px",
                {
                    lineHeight: "1.5em",
                },
            ],
            lg: [
                "18px",
                {
                    lineHeight: "1.333em",
                },
            ],
            xl: [
                "24px",
                {
                    lineHeight: "1.333em",
                },
            ],
            "2xl": [
                "30px",
                {
                    lineHeight: "1.333em",
                },
            ],
            "3xl": [
                "36px",
                {
                    lineHeight: "1.333em",
                },
            ],
            "4xl": [
                "42px",
                {
                    lineHeight: "1.333em",
                },
            ],
            "5xl": [
                "48px",
                {
                    lineHeight: "1.167em",
                },
            ],
        },
        extend: {
            colors: {
                primary: {
                    accent: "#FF2D20",
                    contrast: "#FFFFFF",
                    text: "#5F6464",
                },
                secondary: {
                    light: "#F5F5FA",
                },
                tertiary: {
                    light: "#848487",
                    regular: "#525257",
                    dark: "#090910",
                },
            },
            aspectRatio: {
                photo: "17 / 21",
            },
            boxShadow: {
                custom: "0 10px 15px -3px rgb(0 0 0 / 0.1), 0 0 6px 0 rgb(0 0 0 / 0.05)",
                "3xl": "0 0 32px 0 rgb(0 0 0 / 0.04), 0 0 24px 0 rgb(0 0 0 / 0.04)",
            },
            backgroundImage: {
                "floor-primary":
                    "url(/assets/img/backgrounds/floor-primary.svg)",
            },
        },
    },
    plugins: [require("@tailwindcss/forms")],
};
