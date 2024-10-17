<?php

namespace App\Filament\Resources\Management\ProyectResource\Pages;

use App\Filament\Resources\Management\ProyectResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageProyects extends ManageRecords
{
    protected static string $resource = ProyectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
