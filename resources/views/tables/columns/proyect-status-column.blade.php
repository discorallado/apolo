<div>
	@switch($getState())
		@case('inactivo')
			INACTIVO
			<x-filament::button wire:click="customMethod">
				New user
			</x-filament::button>
		@break

		@case('activo')
			ACTIVO
		@break

		@case('finalizar')
			FINALIZAR
		@break

		@case('finalizado')
			FINALIZADO
		@break

		@default
	@endswitch
</div>

<x-filament-actions::modals />
