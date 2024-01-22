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
          '50': '#eff3fe',
          '100': '#e2e7fd',
          '200': '#cbd3fa',
          '300': '#abb6f6',
          '400': '#8a90ef',
          '500': '#706ee6',
          '600': '#5d52d9',
          '700': '#5043bf',
          '800': '#41399a',
          '900': '#312d6a',
          '950': '#221f47',
        },
      }
    },
  },
  daisyui: {
  },
}

