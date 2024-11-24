<?php

namespace App\Filament\Resources\Management\MovementResource\Pages;

use App\Filament\Resources\Management\MovementResource;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;
use JoseEspinal\RecordNavigation\Traits\HasRecordNavigation;

class ViewMovement extends ViewRecord
{
    use HasRecordNavigation;

    protected static string $resource = MovementResource::class;

    protected function getHeaderActions(): array
    {
        $existingActions = [
            Actions\EditAction::make()
                ->stickyModalHeader()
                ->stickyModalFooter()
                ->using(function (Model $record, array $data): Model {
                    $data['id_proyecto'] = $data['proyect_data']['id'];
                    $data['factura'] = $data['factura_pendiente'] ? 'PEND' : $data['factura'];
                    $data['cargo'] = ($data['tipo'] == 'VENTA') ? $data['valor'] : null;
                    $data['ingreso'] = ($data['tipo'] == 'PAGO') ? $data['valor'] : null;
                    // dd($data);
                    $record->update($data);
                    return $record;
                }),
        ];

        return array_merge($existingActions, $this->getNavigationActions());
    }
}