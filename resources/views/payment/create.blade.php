@extends('layouts.base')

@section('body')
    <x-nav-bar back />
    <div class="font-bold text-xl mb-2 text-center mb-8 uppercase">Paiement</div>
    @livewire('payment-form', ["product" => $product, "service" => $service])
@endsection
