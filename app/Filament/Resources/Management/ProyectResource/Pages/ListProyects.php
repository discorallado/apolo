<?php

namespace App\Filament\Resources\Management\ProyectResource\Pages;

use App\Filament\Resources\Management\ProyectResource;
use App\Filament\Resources\Management\ProyectResource\Widgets\ProyectStatsWidget;
use App\Models\Management\Proyect;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Resources\Pages\ListRecords;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class ListProyects extends ListRecords
{
    protected static string $resource = ProyectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver()
                ->outlined(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProyectStatsWidget::class,
        ];
    }
}
