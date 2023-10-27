<div {{ $attributes }}>
    <div
        class="flex flex-col rounded-2xl items-start justify-between @if ($border) ring-1 ring-inset ring-gray-200 @endif @if($spacy) p-4 @endif">
        <div class="@if($border) @endif w-full text-center lg:text-left">
            @if (!empty($title))
                <h3
                    class="@if (!empty($image)) mb-4 @else mb-2 @endif text-lg font-semibold leading-6 text-gray-900 group-hover:text-gray-600">
                    {{ $title }}
                </h3>
            @endif
            @if (!empty($image))
                <div class="w-full mb-4 @if($imgSmall) bg-gray-100 p-8 rounded-md @else p-4 @endif">
                    <img src="{{ $image }}" class="mx-auto @if($imgSmall) w-32 @endif" />
                </div>
            @endif
            <div class="group relative">
                @if (!empty($description))
                    <p class="line-clamp-3 text-sm leading-6 text-gray-600 @if($spacy) my-4 @else px-4 @endif">
                        {{ $description }}
                    </p>
                @endif
                @if(!empty($action))
                <p>
                    {{ $action }}
                </p>
                @endif
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
