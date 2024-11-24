<div
	{{ $attributes->merge($getExtraAttributes(), escape: false)->class(['fi-ta-text text-sm grid w-max-fit gap-y-1', 'px-3 py-4' => !$isInline()]) }}>
	@if ($getState() === 'VENTA')
		{{-- <x-filament::badge color="warning" icon="heroicon-o-arrow-turn-right-up" icon-position="before"> --}}
		<x-filament::badge color="warning">
			VENTA
		</x-filament::badge>
	@elseif ($getState() === 'PAGO')
		<x-filament::badge color="success">
			PAGO
		</x-filament::badge>
	@endif
</div>
