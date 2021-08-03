const colors = require('tailwindcss/colors')

module.exports = {
  mode: 'jit',
  purge: [
    'templates/**/*twig'
  ],
  darkMode: false, // or 'media' or 'class'
  theme: {
    colors: {
      black: 'black',
      white: 'white',
      current: 'currentColor',
      transparent: 'transparent',

      // gray: colors.coolGray,
      // gray: colors.gray,
      gray: colors.trueGray,
      // gray: colors.warmGray,

      red: colors.red,
      orange: colors.orange,
      amber: colors.amber,
      yellow: colors.yellow,
      lime: colors.lime,
      green: colors.green,
      emerald: colors.emerald,
      teal: colors.teal,
      cyan: colors.cyan,
      sky: colors.sky,
      blue: colors.blue,
      indigo: colors.indigo,
      violet: colors.violet,
      purple: colors.purple,
      fuchsia: colors.fuchsia,
      pink: colors.pink,
      rose: colors.rose,
    },
    extend: {},
    fontFamily: {
      'mono' : ['"IBM Plex Mono"', 'sans-serif'],
      'sans' : ['"IBM Plex Sans"', 'sans-serif']
    }
  },
  variants: {
    extend: {},
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
    require('@tailwindcss/aspect-ratio'),
  ],
}
