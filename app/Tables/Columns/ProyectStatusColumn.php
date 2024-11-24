<?php

namespace App\Tables\Columns;

use App\Filament\Resources\Management\CustomerResource;
use App\Filament\Resources\Management\ProyectResource;
use App\Models\Management\Proyect;
use Closure;
// use Filament\Actions\Action as ActionsAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Column;
use Illuminate\Database\Eloquent\Model;

class ProyectStatusColumn extends Column
{

    protected string $view = 'tables.columns.proyect-status-column';

    // public function getState(Type $args): void {
    //     # code...
    // }

    public function customMethod()
    {
        return Action::make('finalizar')
            ->modalHeading(fn(Model $record): string => 'Finalizar proyecto "' . $record->titulo . '" de ' .  $record->customer->nombre)
            ->modalSubmitActionLabel('Guardar')
            ->form(function (Model $record) {
                $relatedMovements = $record->movements;
                if ($relatedMovements->count() > 0) {
                    $relatedCargos = $relatedMovements->sum('cargo');
                    $relatedIngresos = $relatedMovements->sum('ingreso');
                    $diff = $relatedCargos - $relatedIngresos;
                    $color = $diff > 0 ? 'warning' : (($relatedIngresos > 0) ? 'success' : 'danger');
                    $mensaje = $diff > 0 ? 'Verifica que no exista deuda antes de cambiar el estado del proyecto.' : (($relatedIngresos > 0) ? 'Deuda ok.' : '¡El proyecto no registra pagos! Verifica que no exista deuda antes de cambiar el estado del proyecto.');
                    return [
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Placeholder::make('cargos')
                                    ->content('$' . number_format($relatedCargos, 0, 0, '.')),
                                Forms\Components\Placeholder::make('ingresos')
                                    ->content('$' . number_format($relatedIngresos, 0, 0, '.')),
                                Forms\Components\Placeholder::make('deuda')
                                    ->content('$' . number_format($diff, 0, 0, '.')),
                            ]),
                        Forms\Components\ViewField::make('mensaje')
                            ->view('forms.components.aviso')
                            ->viewData([
                                'color' => $color,
                                'aviso' => $mensaje,
                            ])
                    ];
                }
            })
            ->action(function (array $data, Model $record): void {
                $proyect = Proyect::find($record->id);
                $proyect->estado = 1;
                $proyect->save();
            })
            ->requiresConfirmation();
        // ->disabled(function (?Model $record) {
        //     $estado = ProyectResource::estadoPagos($record);
        //     if ($record->estado) {
        //         return true;
        //     } else {
        //         if (!empty($estado)) {
        //             if ($estado['diff'] > 0) {
        //                 return false;
        //             } else {
        //                 if ($estado['ingresos'] > 0) {
        //                     return false;
        //                 } else {
        //                     return true;
        //                 }
        //             }
        //         } else {
        //             return true;
        //         }
        //     }
        // })
        // ->label(function (Model $record) {
        //     $estado = ProyectResource::estadoPagos($record);
        //     if ($record->estado) {
        //         return 'Finalizado';
        //     } else {
        //         if (!empty($estado)) {
        //             if ($estado['diff'] > 0) {
        //                 return 'Activo';
        //             } else {
        //                 if ($estado['ingresos'] > 0) {
        //                     return 'Finalizar';
        //                 } else {
        //                     return 'Activo';
        //                 }
        //             }
        //         } else {
        //             return 'Inactivo';
        //         }
        //     }
        // })
        // ->icon(function (Model $record) {
        //     $estado = ProyectResource::estadoPagos($record);
        //     if ($record->estado) {
        //         return 'heroicon-o-check-badge';
        //     } else {
        //         if (!empty($estado)) {
        //             if ($estado['diff'] > 0) {
        //                 return 'heroicon-s-banknotes';
        //             } else {
        //                 if ($estado['ingresos'] > 0) {
        //                     return 'heroicon-s-check-circle';
        //                 } else {
        //                     return 'heroicon-s-banknotes';
        //                 }
        //             }
        //         } else {
        //             return 'heroicon-s-exclamation-triangle';
        //         }
        //     }
        // })
        // ->iconPosition(IconPosition::After)
        // ->color(function (Model $record) {
        //     $estado = ProyectResource::estadoPagos($record);
        //     if ($record->estado) {
        //         return 'primary';
        //     } else {
        //         if (!empty($estado)) {
        //             if ($estado['diff'] > 0) {
        //                 return 'warning';
        //             } else {
        //                 if ($estado['ingresos'] > 0) {
        //                     return 'success';
        //                 } else {
        //                     return 'warning';
        //                 }
        //             }
        //         } else {
        //             return 'danger';
        //         }
        //     }
        // })
        // ->hidden(CustomerResource\RelationManagers\ProyectsRelationManager::class)
        // ->button()
        // ->outlined()

    }
}