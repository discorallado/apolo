<div class="w-96 fi-reports-action-panel">

    <div class="flex w-full">
        <div class="flex-grow">
            <p class="text-xl">{{__('filament-reports::reports.filters')}}</p>
        </div>
        <x-filament::dropdown>
            <x-slot name="trigger">
                <x-filament::icon-button icon="heroicon-o-cog-8-tooth"/>
            </x-slot>

            <x-filament::dropdown.list>
                {{-- <x-filament::dropdown.list.item wire:click="exportToPdf">
                    To PDF
                </x-filament::dropdown.list.item>

                <x-filament::dropdown.list.item @click="$exportToExcel()">
                    To Excel
                </x-filament::dropdown.list.item> --}}

                <x-filament::dropdown.list.item @click="$printReport()"
                icon="heroicon-o-printer">
                    {{ __('filament-reports::reports.print') }}
                </x-filament::dropdown.list.item>
            </x-filament::dropdown.list>
        </x-filament::dropdown>
    </div>
    <div>
        <x-filament-panels::form :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()" wire:submit="filter">
            {{ $this->filterForm }}

            <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" :full-width="$this->hasFullWidthFormActions()" />
        </x-filament-panels::form>

    </div>
</div>
