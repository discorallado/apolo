<?php

namespace App\Filament\Resources\HR\BinnacleResource\Pages;

use App\Filament\Resources\HR\BinnacleResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;


class ManageBinnacles extends ManageRecords
{

    protected static string $resource = BinnacleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make()
            //     ->label('Nuevo registro'),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        return [
            BinnacleResource\Widgets\CalendarWidget2::class,
        ];
    }
}
