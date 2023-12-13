<nav class="mb-4">
    <div class="mx-auto">
        <div class="flex h-16 justify-between">
            <div class="flex">
                <div class="-ml-2 mr-2 flex items-center md:hidden">
                    <!-- Mobile menu button -->
                    <button type="button"
                        class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500"
                        aria-controls="mobile-menu" aria-expanded="false">
                        <span class="absolute -inset-0.5"></span>
                        <span class="sr-only">Open main menu</span>
                        <!--
              Icon when menu is closed.

              Menu open: "hidden", Menu closed: "block"
            -->
                        <svg class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <!--
              Icon when menu is open.

              Menu open: "block", Menu closed: "hidden"
            -->
                        <svg class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex flex-shrink-0 items-center">
                    <h1 class="font-bold text-primary-950 text-2xl italic">
                        <a href="{{ route('service.index') }}" class="flex">
                            <img src="{{ Vite::asset('resources/images/icon.png') }}" class="w-16 h-16" />
                            <div class="mt-4">{{ config('app.name') }}</div>
                        </a>
                    </h1>
                </div>
            </div>
            <div class="hidden md:ml-6 md:flex md:space-x-8">
                <!-- Current: "border-primary-500 text-gray-900", Default: "border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700" -->
                <a href="#" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900">Comment
                    ça marche?</a>
                <a href="#"
                    class="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">FAQ</a>
                <a href="#"
                    class="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">Contact</a>

            </div>
        </div>
    </div>

    <!-- Mobile menu, show/hide based on menu state. -->
    <div class="md:hidden hidden" id="mobile-menu">
        <div class="space-y-1 pb-3 pt-2">
            <!-- Current: "bg-primary-50 border-primary-500 text-primary-700", Default: "border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700" -->
            <a href="#"
                class="block border-l-4 border-primary-500 bg-primary-50 py-2 pl-3 pr-4 text-base font-medium text-primary-700 sm:pl-5 sm:pr-6">Dashboard</a>
            <a href="#"
                class="block border-l-4 border-transparent py-2 pl-3 pr-4 text-base font-medium text-gray-500 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-700 sm:pl-5 sm:pr-6">Team</a>
            <a href="#"
                class="block border-l-4 border-transparent py-2 pl-3 pr-4 text-base font-medium text-gray-500 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-700 sm:pl-5 sm:pr-6">Projects</a>
            <a href="#"
                class="block border-l-4 border-transparent py-2 pl-3 pr-4 text-base font-medium text-gray-500 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-700 sm:pl-5 sm:pr-6">Calendar</a>
        </div>
    </div>
</nav>
