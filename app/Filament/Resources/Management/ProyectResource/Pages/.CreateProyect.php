<?php

namespace App\Filament\Resources\Management\ProyectResource\Pages;

use App\Filament\Resources\Management\ProyectResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProyect extends CreateRecord
{
    protected static string $resource = ProyectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }
}
