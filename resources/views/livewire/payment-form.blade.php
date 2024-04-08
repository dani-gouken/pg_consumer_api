<div>
    <form method="POST"
        action="{{ route('payment.store', ['service' => $service->slug, 'product' => $product->slug]) }}">
        @csrf
        @include('partials.alert')
        <div class="grid lg:grid-cols-12 lg:gap-16">
            <x-service-card title="{{ $product->name }}" :border="false" class="lg:col-span-5 col-span-12 mb-4 lg:mb-0">
                <x-service-card :image="Vite::asset($service->logo_url)" :description="$product->description" img-small class="mb-6">
                    <p class="text-xl text-primary-700 my-8 font-bold text-center">
                        @if ($amount)
                            @if (!empty($selectedOptions))
                                {{ format_amount($amount) }} <br /> ({{ format_amount($product->price) }} + Options)
                            @else
                                {{ format_amount($amount) }}
                            @endif
                        @endif
                    </p>
                </x-service-card>
            </x-service-card>
            <x-service-card :border="false" class="lg:col-span-7 col-span-12">
                <x-service-card title="Vos informations" :border="false">
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    @if ($searchResult)
                        <input type="hidden" name="credit_destination" value="{{ $searchResult->customerNumber }}" />
                        <div class="mt-4 lg:flex justify-between w-full">
                            <div>
                                <p class="font-semibold">
                                    {{ $searchResult->customerName }}
                                </p>
                                <p class="italic text-sm">
                                    {{ $searchResult->customerNumber }}
                                </p>
                                <p class="italic text-sm">
                                    {{ $searchResult->billNumber }}
                                </p>
                            </div>
                            <div class="mt-4 lg:mt-0">
                                <a class="btn-sm btn btn-outline btn-primary"
                                    href="{{ route('search.index', [$service->slug, $product->slug]) }}">Changer</a>
                            </div>
                        </div>
                    @else
                        <x-input :label="$service->form_input_label" type="text" name="credit_destination" :readonly="!$editable"
                            :value="$creditDestination" required pattern="{{ $service->form_input_regex }}" :placeholder="$service->form_input_placeholder"
                            class="mt-4" />
                    @endif

                    @if (!$product->fixed_price)
                        <x-input min='{{ $service->min_amount }}' max='{{ $service->max_amount }}' label="Montant"
                            type="number" name="amount" required
                            placeholder="Min: {{ number_format($service->min_amount, 0, ',', ' ') }}, Max: {{ number_format($service->max_amount, 0, ',', ' ') }}"
                            class="mt-4" />
                    @endif
                    @if ($product->fixed_price && $product->options->isNotEmpty())
                        <div wire:ignore>
                            <select class="mt-8" id="options" name="options[]" wire:model.lazy="selectedOptions"
                                placeholder="Options">
                                @foreach ($product->options as $option)
                                    <option value="{{ $option->id }}">
                                        {{ $option->name }}&nbsp;&nbsp;({{ $option->formatted_amount }} FCFA)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </x-service-card>
                <hr class="my-6 h-0.5 border-t-0 bg-neutral-100 opacity-100 dark:opacity-50" />
                <x-service-card title="Informations de paiement" :border="false">
                    <x-input label="Numéro mobile money" type="phone" name="debit_destination" pattern="^[0-9]{9}$"
                        required placeholder="Ex: 6XXXXXXXX" class="mt-4" />
                </x-service-card>
                <hr class="my-6 h-0.5 border-t-0 bg-neutral-100 opacity-100 dark:opacity-50" />
                <button type="submit" class="btn btn-primary w-full">Payer</button>
            </x-service-card>
        </div>
    </form>
</div>
