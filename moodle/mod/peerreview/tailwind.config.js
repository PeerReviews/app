/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './**/*.php',
    './**/*.html',
    './**/*.js',
    './**/*.mustache'
  ],
  theme: {
    extend: {
      boxShadow: {
        'md-lg': '0 6px 10px rgba(0, 0, 0, 0.1), 0 2px 5px rgba(0, 0, 0, 0.05)',  // Ombre personnalisée
      },
    },
  },
  plugins: [],
}
