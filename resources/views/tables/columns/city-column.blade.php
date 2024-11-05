<div
    {{
        $attributes
            ->merge($getExtraAttributes(), escape: false)
            ->class([
                'fi-ta-text text-sm grid w-full gap-y-1',
                'px-3 py-4' => ! $isInline(),
            ])
    }}
>{{ $generalSettings['comunas'][(int)$getState()] }}</div>
