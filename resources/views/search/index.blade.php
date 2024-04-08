@extends('layouts.base')

@section('body')
    <x-nav-bar back />
    <div class="font-bold text-xl mb-2 text-center mb-8 uppercase">{{ $service->name }} ({{ $product->name }})</div>
    <form method="POST" action="{{ route('search.index', ['service' => $service->slug, 'product' => $product->slug]) }}">
        @csrf
        @include('partials.alert')
        <div class="text-center max-w-full">
            <x-service-card :border="false">
                <x-service-card :border="false" class="mb-4">
                    <x-input center :value="$query" :label="'Entrez votre ' . $service->form_input_label" type="text" name="q" :placeholder="$service->form_input_placeholder" class="mt-4" />
                </x-service-card>
                <button type="submit" class="btn btn-primary w-full">Rechercher</button>
            </x-service-card>
        </div>
    </form>
    @if (isset($results) && $results->isNotEmpty())
        <div class="mt-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-base font-semibold leading-6 text-gray-900">Résultat(s)</h1>
                </div>
            </div>
            <div class="mt-4 flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <table class="min-w-full divide-y divide-gray-300">
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($results as $result)
                                    <tr>
                                        <td
                                            class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">
                                            <a href="{{ $result->paymentUrl($service, $product) }}"
                                                class="text-indigo-600 hover:text-indigo-900">{{ $result->customerName }}
                                                ({{ $result->customerNumber }})</a>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ $result->billNumber }}
                                            @if ($result->hasProduct())
                                                ({{ $result->getProduct()->name }})
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
