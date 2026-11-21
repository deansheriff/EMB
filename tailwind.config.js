/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./app/**/*.php",
    "./public/**/*.php",
    "./views/**/*.php"
  ],
  theme: {
    extend: {
      colors: {
        ink: "rgb(var(--color-ink) / <alpha-value>)",
        muted: "rgb(var(--color-muted) / <alpha-value>)",
        ivory: "rgb(var(--color-ivory) / <alpha-value>)",
        blush: "rgb(var(--color-blush) / <alpha-value>)",
        wine: "rgb(var(--color-wine) / <alpha-value>)",
        berry: "rgb(var(--color-berry) / <alpha-value>)",
        sage: "rgb(var(--color-sage) / <alpha-value>)",
        amber: "rgb(var(--color-amber) / <alpha-value>)",
        line: "rgb(var(--color-line) / <alpha-value>)"
      },
      fontFamily: {
        display: ["Newsreader", "Georgia", "serif"],
        sans: ["Manrope", "Arial", "sans-serif"]
      },
      boxShadow: {
        soft: "0 16px 50px rgba(72, 43, 50, .10)"
      },
      maxWidth: {
        content: "1240px"
      }
    }
  },
  plugins: [require("@tailwindcss/forms")]
};

