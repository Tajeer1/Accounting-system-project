@props(['label' => null, 'name', 'value' => null, 'rows' => 3, 'required' => false])

<div {{ $attributes->only('class')->merge(['class' => 'space-y-1.5']) }}>
    @if ($label)
        <label for="{{ $name }}" class="block text-xs font-medium text-slate-700">
            {{ $label }} @if ($required)<span class="text-rose-500">*</span>@endif
        </label>
    @endif
    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        @if($required) required @endif
        {{ $attributes->except('class') }}
        class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent placeholder:text-slate-400 @error($name) border-rose-300 @enderror"
    >{{ old($name, $value) }}</textarea>
    @error($name)
        <p class="text-[11px] text-rose-600">{{ $message }}</p>
    @enderror
</div>
