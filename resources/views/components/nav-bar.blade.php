<div>
    <div class="grid grid-cols-3">
        @if ($back)
            <div>
                <button class="btn bg-gray-200 items-center font-regular text-sm hidden lg:flex"
                    onclick="window.history.back()">
                    <div class="mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                        </svg>
                    </div>
                    Back
                </button>
            </div>
        @endif
        @if (!empty($title))
            <div class="@if (!$back) col-span-3 @endif">
                <h2 class="text-lg text-primary-900 text-center">{{ $title }}</h2>
            </div>
        @endif
    </div>
</div>
