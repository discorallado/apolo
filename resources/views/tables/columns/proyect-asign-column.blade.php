<div class="justify-center text-center">
    @if (is_null($getState()))
        <x-filament::badge color="warning" icon="heroicon-o-exclamation-triangle" icon-position="before">
            S/P
        </x-filament::badge>
    @else
        <x-filament::badge color="success" icon="heroicon-o-document-check" icon-position="before"
            tooltip="{{ $getRecord()->proyect->customer->nombre }}">
            Asignado
        </x-filament::badge>
    @endif
</div>
