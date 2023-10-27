@extends('layouts.base')

@section('body')
    <x-nav-bar back />
    <div class="font-bold text-xl mb-2 text-center mb-8 uppercase">Paiement</div>
    <div class="grid lg:grid-cols-12 lg:gap-16">
        <x-service-card title="{{ $product->name }}" :border="false" class="col-span-5 mb-4 lg:mb-0">
            <x-service-card :image="Vite::asset($service->logo_url)" :description="$product->description" img-small class="mb-6">
                <p class="text-xl text-primary-700 my-8 font-bold text-center">
                    @if ($product->fixed_price)
                        {{ $product->formatted_price }} FCFA
                    @endif
                </p>
            </x-service-card>
        </x-service-card>
        <x-service-card :border="false" class="col-span-7">
            <form method="POST"
                action="{{ route('payment.store', ['service' => $service->slug, 'product' => $product->slug]) }}">
                @csrf
                @include('partials.alert')
                <x-service-card title="Vos informations" :border="false">
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <x-input :label="$service->form_input_label" type="text" name="credit_destination" required
                        pattern="{{ $service->form_input_regex }}" :placeholder="$service->form_input_placeholder" class="mt-4" />
                    @if (!$service->fixed_price)
                        <x-input
                            min='{{ $service->min_amount }}'
                            max='{{ $service->max_amount }}'
                            label="Montant" type="number" name="amount" required
                            placeholder="Min: {{ number_format($service->min_amount, 0, ',', ' ') }}, Max: {{ number_format($service->max_amount, 0, ',', ' ') }}"
                            class="mt-4" />
                    @endif
                </x-service-card>
                <hr class="my-6 h-0.5 border-t-0 bg-neutral-100 opacity-100 dark:opacity-50" />
                <x-service-card title="Informations de paiement" :border="false" class="mb-8">
                    <x-input label="Numéro mobile money" type="phone" name="debit_destination" pattern="^[0-9]{9}$"
                        required placeholder="Ex: 6XXXXXXXX" class="mt-4" />
                </x-service-card>
                <button type="submit" class="btn btn-primary w-full">Payer</button>
            </form>
        </x-service-card>
    </div>
@endsection
