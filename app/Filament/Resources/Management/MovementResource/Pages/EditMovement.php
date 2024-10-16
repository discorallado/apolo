<?php

namespace App\Filament\Resources\Management\MovementResource\Pages;

use App\Filament\Resources\Management\MovementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMovement extends EditRecord
{

    protected static string $resource = MovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // PublishAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (is_numeric($data['factura'])) {
            $data['tipo_factura'] = 'numero';
        } else {
            $data['tipo_factura'] = $data['factura'];
        }
        return $data;
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['tipo_factura'] != 'numero') {
            $data['factura'] = $data['tipo_factura'];
        }
        $data['id_proyecto'] = $data['proyect_data']['id'];
        unset($data['tipo_factura']);
        unset($data['proyect_data']);
        unset($data['cliente']);
        return $data;
    }
}
