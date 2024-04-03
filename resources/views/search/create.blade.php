@extends('layouts.base')

@section('body')
    <x-nav-bar back />
    <div class="font-bold text-xl mb-2 text-center mb-8 uppercase">{{ $service->name }} ({{ $product->name }})</div>
    <form method="POST" action="{{ route('search.store', ['service' => $service->slug, 'product' => $product->slug]) }}">
        @csrf
        @include('partials.alert')
        <div class="text-center max-w-full">
            <x-service-card :border="false">
                <x-service-card :border="false" class="mb-4">
                    <x-input center :label="'Entrez votre ' . $service->form_input_label" type="text" name="q" :placeholder="$service->form_input_placeholder" class="mt-4" />
                </x-service-card>
                <button type="submit" class="btn btn-primary w-full">Rechercher</button>
            </x-service-card>
        </div>
    </form>
@endsection
