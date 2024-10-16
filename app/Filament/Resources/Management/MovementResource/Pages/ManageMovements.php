<?php

namespace App\Filament\Resources\Management\MovementResource\Pages;

use App\Filament\Resources\Management\MovementResource;
use App\Models\Management\Movement;
use Closure;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;

class ManageMovements extends ManageRecords
{
    protected static string $resource = MovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->extraModalFooterActions(fn (Action $action): array => [
                    $action->makeModalSubmitAction('createAnother', arguments: ['another' => true]),
                ]),

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
    protected function mutateFormDataBeforeFill(array $data): array
    {
        dd($data);
        if (is_numeric($data['factura'])) {
            $data['tipo_factura'] = 'numero';
        } else {
            $data['tipo_factura'] = $data['factura'];
        }
        // dd($data);
        return $data;
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {
        //   $data['periodo'] = date('m', strtotime($data['fecha']));
        //   $data['ano'] = date('y', strtotime($data['fecha']));
        //   $data['user_id'] = auth()->id();
        //   if ($data['tipo_factura'] != 'numero') {
        //         $data['factura'] = $data['tipo_factura'];
        //       }
        //   unset($data['cliente']);
        //   dd($data);

        //   $data['mes'] = date('m', strtotime($data['fecha']));
        //   $data['ano'] = date('y', strtotime($data['fecha']));
        // dd($data);

        if ($data['tipo_factura'] != 'numero') {
            $data['factura'] = $data['tipo_factura'];
        }
        unset($data['tipo_factura']);
        // dd($data);
        $data['id_proyecto'] = $data['proyect_data']['id'];
        unset($data['proyect_data']);
        unset($data['cliente']);

        return $data;
        return $data;
    }
}
