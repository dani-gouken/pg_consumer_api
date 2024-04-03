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
          '50': '#f2f9fd',
          '100': '#e4f0fa',
          '200': '#c2e1f5',
          '300': '#8ccbed',
          '400': '#4fafe1',
          '500': '#2589bd',
          '600': '#1a77af',
          '700': '#165f8e',
          '800': '#165176',
          '900': '#184562',
          '950': '#102b41',
        },
      }
    },
  },
  daisyui: {
  },
}

