<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }">
        <div {{ $attributes ->merge([
            'id' => $getId(),
            ], escape: false)
            ->merge($getExtraAttributes(), escape: false)
            }}
            >
            {{ $getChildComponentContainer() }}
        </div>
    </div>
</x-dynamic-component>
