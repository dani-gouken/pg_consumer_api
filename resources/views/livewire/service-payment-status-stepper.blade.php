<div wire:poll.750ms>
    <nav aria-label="Progress">
        <ol role="list" class="overflow-hidden">
            @foreach ($steps as $step)
                @php
                    $status = $step['status'];
                @endphp
                <li class="relative @if (!$loop->last) pb-10 @endif">
                    @if (!$loop->last)
                        <div class="absolute left-4 top-4 -ml-px mt-0.5 h-full w-0.5 {{ $this->stepBarColor($status) }}"
                            aria-hidden="true">
                        </div>
                    @endif
                    <!-- Complete Step -->
                    <a href="#" class="group relative flex items-start">
                        <span class="flex h-9 items-center">
                            <span
                                class="relative z-10 flex h-8 w-8 items-center justify-center rounded-full {{ $this->stepIconClass($status) }}">
                                {!! $this->stepIcon($status) !!}
                            </span>
                        </span>
                        <span class="ml-4 flex min-w-0 w-full flex-col">
                            <span class="text-sm font-medium">{{ $step['title'] }}</span>
                            <span class="text-sm text-gray-500">
                                {{ $step['description'] }}
                            </span>
                        </span>
                    </a>
                </li>
            @endforeach
            {{-- 
            <li class="relative pb-10">
                <div class="absolute left-4 top-4 -ml-px mt-0.5 h-full w-0.5 bg-gray-300" aria-hidden="true"></div>
                <!-- Current Step -->
                <a href="#" class="group relative flex items-start" aria-current="step">
                    <span class="flex h-9 items-center" aria-hidden="true">
                        <span
                            class="relative z-10 flex h-8 w-8 items-center justify-center rounded-full border-2 border-indigo-600 bg-white">
                            <span class="h-2.5 w-2.5 rounded-full bg-indigo-600"></span>
                        </span>
                    </span>
                    <span class="ml-4 flex min-w-0 flex-col">
                        <span class="text-sm font-medium text-indigo-600">Paiement en attente</span>
                        <span class="text-sm text-gray-500">Tapez #150#, puis 1 pour confirmer le paiement</span>
                    </span>
                </a>
            </li>
            <li class="relative">
                <!-- Upcoming Step -->
                <a href="#" class="group relative flex items-start">
                    <span class="flex h-9 items-center" aria-hidden="true">
                        <span
                            class="relative z-10 flex h-8 w-8 items-center justify-center rounded-full border-2 border-gray-300 bg-white group-hover:border-gray-400">
                            <span class="h-2.5 w-2.5 rounded-full bg-transparent group-hover:bg-gray-300"></span>
                        </span>
                    </span>
                    <span class="ml-4 flex min-w-0 flex-col">
                        <span class="text-sm font-medium text-gray-500">Service acheté</span>
                        <span class="text-sm text-gray-500">Votre abonnement à été acheté</span>
                    </span>
                </a>
            </li> --}}
        </ol>
    </nav>
</div>
