<?php

namespace App\Filament\Resources\Management\ProyectResource\Pages;

use App\Filament\Resources\Management\ProyectResource;
use App\Filament\Resources\Management\ProyectResource\Widgets\ProyectStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProyects extends ListRecords
{
    protected static string $resource = ProyectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProyectStatsWidget::class,
        ];
    }
}
