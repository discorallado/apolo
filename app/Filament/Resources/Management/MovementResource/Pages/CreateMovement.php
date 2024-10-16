<?php

namespace App\Filament\Resources\Management\MovementResource\Pages;

use App\Filament\Resources\Management\MovementResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMovement extends CreateRecord
{
    protected static string $resource = MovementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data['tipo_factura'] != 'numero') {
            $data['factura'] = $data['tipo_factura'];
        }
        $data['id_proyecto'] = $data['proyect_data']['id'];
        unset($data['proyect_data']);
        unset($data['tipo_factura']);
        unset($data['cliente']);
        $data['user_id'] = auth()->id();
        return $data;
    }
}
