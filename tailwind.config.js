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
          '50': '#e7f6ff',
          '100': '#d3efff',
          '200': '#b0dfff',
          '300': '#81c7ff',
          '400': '#4fa0ff',
          '500': '#2877ff',
          '600': '#044aff',
          '700': '#0048ff',
          '800': '#003dd9',
          '900': '#0b39a4',
          '950': '#07205f',
        },
      }
    },
  },
  daisyui: {
  },
}

