@extends('layouts.base')

@section('body')
    <x-nav-bar :title="$service->name" back />
    <div class="font-bold text-xl mb-2 text-center mb-8 uppercase">Choisir un produit</div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ($products as $product)
            <x-service-card spacy :title="$product->name" :description="$product->description">
                <x-slot:action>
                    <a href="{{ route('payment.create', ['product' => $product->slug, 'service' => $service->slug]) }}"
                        class="btn btn-primary w-full">Choisir</a>
                </x-slot:action>
            </x-service-card>
        @endforeach
    </div>
@endsection

@section('navbar-center')
    <img src="{{ Vite::asset("resources/images/logos/{$service->image}") }}" class="w-32"
        alt="{{ $service->name }} logo" />
@endsection

@section('navbar-end')
    <a href="{{ route('service.index') }}" class="btn btn-secondary">Retour</a>
@endsection
