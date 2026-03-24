@props([
    'name' => '',
    'options' => [],
    'selected' => null,
    'label' => '',
    'placeholder' => 'Chọn...',
    'required' => false,
    'error' => null,
    'onchange' => '',
    'isShowError' => false,
    'className' => '',
])

@php
    $hasError = $errors->has($name) || $error;
@endphp

<div class="custom-select {{ $hasError ? 'has-error' : '' }}" data-custom-select>
    <input 
        type="hidden" 
        name="{{ $name }}" 
        value="{{ old($name, $selected) }}" 
    />
    
    @if($label)
        <div class="rsvp-label">
            {!! $label !!}
            @if($required)<span class="text-red-500">*</span>@endif
        </div>
    @endif
    
    <div 
        class="custom-select-trigger rsvp-select {{ $className }} {{ $hasError ? 'is-invalid' : '' }}"
        role="combobox"
        aria-haspopup="listbox"
        aria-expanded="false"
        tabindex="0"
        @if($onchange) data-onchange="{{ $onchange }}" @endif
    >
        {{ $placeholder }}
    </div>
    
    <div class="custom-select-dropdown" role="listbox">
        @if($placeholder)
            <div 
                class="custom-select-option btn-disabled"         
            >
                {{ $placeholder }}
            </div>
        @endif
        
        @foreach($options as $key => $option)
            @php
                $value = is_object($option) ? ($option->id ?? $key) : $key;
                $text = is_object($option) ? ($option->name ?? $option->title ?? $option) : $option;
                $isSelected = (string) old($name, $selected) === (string) $value;
            @endphp
            <div 
                class="custom-select-option {{ $isSelected ? 'selected' : '' }}" 
                data-value="{{ $value }}"
                role="option"
                aria-selected="{{ $isSelected ? 'true' : 'false' }}"
            >
                {{ $text }}
            </div>
        @endforeach
    </div>
    
    @if($hasError && $isShowError)
        <div class="mt-1 text-xs text-red-400">
            {{ $errors->first($name) ?? $error }}
        </div>
    @endif
</div>