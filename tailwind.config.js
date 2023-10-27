/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  safelist: [
    'h-2.5',
    'w-2.5',
    'rounded-full',
    'bg-transparent',
    'group-hover:bg-gray-300',
    'bg-indigo-600',
    'h-5',
    'w-5',
    'text-white',
    'animate-ping',
    'bg-red-600',
    'bg-red-800',
    'text-red-500',
    'bg-green-600'
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ["Plus Jakarta Sans"]
      },
      colors: {
        'primary': {
          '50': '#fdf6fd',
          '100': '#fcebfc',
          '200': '#f8d6f8',
          '300': '#f1b6ee',
          '400': '#e78be0',
          '500': '#d85dcf',
          '600': '#bb3eaf',
          '700': '#9b308f',
          '800': '#7f2974',
          '900': '#69265f',
          '950': '#5b1350',
        },
      }
    },
  },
  daisyui: {
  },
}

