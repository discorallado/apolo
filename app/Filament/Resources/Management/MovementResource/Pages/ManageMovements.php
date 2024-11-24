<?php

namespace App\Filament\Resources\Management\MovementResource\Pages;

use App\Filament\Resources\Management\MovementResource;
use App\Filament\Resources\Management\MovementResource\CreateMovement;
use App\Models\Management\Movement;
use Closure;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use JoseEspinal\RecordNavigation\Traits\HasRecordsList;

class ManageMovements extends ManageRecords
{
    use HasRecordsList;

    protected static string $resource = MovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->createAnother()
                ->stickyModalHeader()
                ->stickyModalFooter()
                ->using(function (array $data, string $model): Model {
                    // dd($data);
                    $data['id_proyecto'] = $data['proyect_data']['id'];
                    $data['factura'] = $data['factura_pendiente'] ? 'PEND' : $data['factura'];
                    $data['cargo'] = ($data['tipo'] == 'VENTA') ? $data['valor'] : null;
                    $data['ingreso'] = ($data['tipo'] == 'PAGO') ? $data['valor'] : null;
                    $data['user_id'] = Auth()->id();
                    return $model::create($data);
                }),

        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 4;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MovementResource\Widgets\MovimientosMesWidget::class,
        ];
    }
}