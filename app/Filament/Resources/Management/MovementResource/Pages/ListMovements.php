<?php

namespace App\Filament\Resources\Management\MovementResource\Pages;

use App\Filament\Resources\Management\MovementResource;
use App\Filament\Resources\Management\MovementResource\Widgets\MovimientosMesWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMovements extends ListRecords
{

    protected static string $resource = MovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 4;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MovimientosMesWidget::class,
        ];
    }
}
