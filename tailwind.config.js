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
        'teal': {
          '1': '#FAFEFD',
          '2': '#F3FBF9',
          '3': '#E0F8F3',
          '4': '#CCF3EA',
          '5': '#B8EAE0',
          '6': '#A1DED2',
          '7': '#83CDC1',
          '8': '#53B9AB',
          '9': '#12A594',
          '10': '#0D9B8A',
          '11': '#008573',
          '12': '#0D3D38'

        }
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
