{{-- <div
	{{ $attributes->merge($getExtraAttributes(), escape: false)->class(['fi-ta-text text-sm grid w-max-fit gap-y-1', 'px-3 py-4' => !$isInline()]) }}> --}}
@if (!is_null($getState()))
	{{-- <x-filament::badge color="success" icon="heroicon-o-arrow-turn-right-down" icon-position="before"> --}}
	COT-{{ $getState() }}
	{{-- </x-filament::badge> --}}
@endif
{{-- </div> --}}
