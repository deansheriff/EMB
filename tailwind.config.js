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
        ink: "#2E2527",
        muted: "#6F6265",
        ivory: "#FCF8F4",
        blush: "#F5E7E9",
        wine: "#6E3345",
        berry: "#9A4F62",
        sage: "#567568",
        amber: "#C68B4C",
        line: "#E8DCDA"
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

