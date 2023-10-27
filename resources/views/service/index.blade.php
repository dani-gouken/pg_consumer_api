@extends('layouts.base')

@section('body')
    <div>
        <div class="font-bold text-xl mb-2 text-center uppercase">Choisir un service</div>
        <div class="mx-auto mt-8 grid max-w-2xl grid-cols-1 gap-x-8 gap-y-20 lg:mx-0 lg:max-w-none lg:grid-cols-3">
            @foreach ($services as $service)
                <x-service-card img-small :image="Vite::asset($service->logo_url)" :description="$service->description">
                    <x-slot:action>
                        <div class="p-4">
                            <a href="{{ route('services.show', ['service' => $service->slug]) }}"
                                class="btn btn-primary w-full">Choisir</a>
                        </div>
                    </x-slot:action>
                </x-service-card>
            @endforeach
        </div>
    </div>
@endsection
