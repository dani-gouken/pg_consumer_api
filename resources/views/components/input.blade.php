<div {{ $attributes }}>
    <div class="w-full mb-6 md:mb-0">
        <label class="block tracking-wide text-gray-700 text-xs uppercase mb-2 {{ $center ? 'text-center' : '' }}"
            for="form-{{ $name }}">
            {{ $label }}
        </label>
        <input @if ($required) required @endif @if ($disabled) disabled @endif @if($readonly) readonly @endif
            @if ($pattern) pattern="{{ $pattern }}" @endif name="{{ $name }}"
            @if ($min) min="{{ $min }}" @endif
            @if ($max) max="{{ $max }}" @endif value="{{ $value ?? old($name) }}"
            class="appearance-none block w-full bg-gray-100 text-gray-700 border  {{ $center ? 'text-center' : '' }} @error($name) border-red-500 @enderror rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
            id="form-{{ $name }}" type="{{ $type }}" placeholder="{{ $placeholder }}">
        @error($name)
            <p class="text-red-500 text-xs italic">{{ $message }}</p>
        @enderror
    </div>
</div>
