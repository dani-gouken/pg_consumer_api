@extends('layouts.base')

@section('body')
    <div>
        <x-nav-bar back />
        <div class="font-bold text-xl mb-2 text-center mb-4 uppercase">Paiement</div>
        <div class="mb-2 text-center mb-8 uppercase"><a>#{{ $payment->code }}</a></div>
        <div class="grid lg:grid-cols-12 lg:gap-16">
            <x-service-card title="{{ $product->name }}" :border="false" class="col-span-12 lg:col-span-5 mb-4 lg:mb-0">
                <x-service-card :image="Vite::asset($service->logo_url)" :description="$product->description" img-small class="mb-6">
                    <p class="text-xl text-primary-700 my-8 font-bold text-center">
                        @if ($product->fixed_price)
                            {{ $product->formatted_price }} FCFA
                        @endif
                    </p>
                </x-service-card>
            </x-service-card>
            <x-service-card :border="false" class="col-span-7" title="DETAILS">
                <div class="my-8">
                    @livewire('service-payment-status-stepper', ['code' => $payment->code])
                </div>
                <div>
                    <table class="min-w-full divide-y divide-gray-300">
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">
                                    {{ $service->form_input_label }}
                                </td>
                                <td class="hidden whitespace-nowrap px-3 py-4 text-sm text-gray-500 sm:table-cell">
                                    {{ $payment->credit_destination }}
                                </td>
                            </tr>
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">
                                    {{ $payment->paymentService->form_input_label }}
                                </td>
                                <td class="hidden whitespace-nowrap px-3 py-4 text-sm text-gray-500 sm:table-cell">
                                    {{ $payment->debit_destination }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-service-card>
        </div>
    </div>
@endsection
