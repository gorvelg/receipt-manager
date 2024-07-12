/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    extend: {
      'colors': {
        'primary': {
          '200': 'rgba(0, 155, 147, 0.1)',
          '300': '#d2e0e042',
        },
        'text': {
          '800': '#083448',
        },
      },
      fontFamily: {
        display: 'Poppins'
      },
      keyframes: {
        wiggle: {
          '0%, 100%': { transform: 'rotate(-3deg)' },
          '50%': { transform: 'rotate(3deg)' },
        }
      }
    },
  },
  plugins: [],
}
