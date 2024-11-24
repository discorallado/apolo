<?php

namespace App\Filament\Resources\HR\PaymentResource\Widgets;

use App\Models\HR\Binnacle;
use App\Models\HR\Payment;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\FileUpload;
use IbrahimBougaoua\FilaProgress\Tables\Columns\ProgressBar;

// use Filament\Forms\Components\TextInput;

class TablaWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Bitacoras (días marcados)';

    protected function getTableQuery(): Builder
    {
        // dd(Payment::query()->where('id_bitacora', '1')->get());

        // dd(Binnacle::query()->whereColumn('id', 'payment.id_bitacora')->get());
        return Binnacle::query();
        // ->withSum('payments', 'monto');

    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'starts_at';
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('worker.nombre')
                ->label('Trabajador')
                ->sortable(),
            Tables\Columns\TextColumn::make('title')
                ->label('Días trabajados')
                ->sortable(),
            Tables\Columns\TextColumn::make('dias')
                ->label('Cantidad')
                ->suffix(' día/s')
                ->sortable(),
            Tables\Columns\TextColumn::make('proyect.titulo')
                ->label('Proyecto')
                ->placeholder('Sin asignar')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('total_dias')
                ->label('Cargo')
                ->money('CLP', 0, 'cl')
                ->placeholder('Sin pago'),
            Tables\Columns\TextColumn::make('payments_sum_monto')
                ->sum('payments', 'monto')
                ->label('Pagado')
                ->money('CLP', 0, 'cl')
                ->placeholder('Sin pago'),
            ProgressBar::make('')
                ->getStateUsing(function ($record) {
                    $total = $record->total_dias;
                    $progress = $record->payments_sum_monto;
                    return [
                        'total' => $total,
                        'progress' => $progress,
                    ];
                })
                ->hideProgressValue(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\TrashedFilter::make(),
        ];
    }
    protected function getTableActions(): array
    {
        return [
            Action::make('pagado')
                ->hidden(fn(Model $record): bool => ($record->payments_sum_monto >= $record->total_dias) ? false : true)
                ->label('Pagado')
                ->button()

                ->size(ActionSize::ExtraSmall)
                ->color('success')
                ->disabled(true)
                ->icon('heroicon-o-check')
                ->url(false),
            CreateAction::make('Pagar')
                ->hidden(fn(Model $record): bool => ($record->payments_sum_monto >= $record->total_dias) ? true : false)
                ->label('Pagar')
                ->button()

                ->size(ActionSize::ExtraSmall)
                ->color('primary')
                ->icon('heroicon-o-banknotes')
                ->modalHeading(fn(Model $record): string => 'Pagar ' . $record->dias . ' día/s a: ' . $record->worker->nombre)
                ->modalSubmitActionLabel('Guardar')
                ->modalFooterActionsAlignment('right')
                ->modalWidth('md')
                ->fillForm(fn(Binnacle $record): array => [
                    'total_dias' => $record->total_dias,
                    'monto' => ($record->total_dias - $record->payments->sum('monto')),
                ])
                ->form(function (?Model $record): array {
                    return [
                        TextInput::make('total_dias'),
                        TextInput::make('monto'),
                        FileUpload::make('attachment')
                            ->directory('payments-attachments')
                    ];
                })
                ->using(function (array $data, ?Model $record): Model {
                    unset($data['total_dias']);
                    $data['fecha'] = now()->format('Y-m-d H:i:s');
                    $data['id_bitacora'] = $record->id;
                    $data['user_id'] = auth()->id();

                    return Payment::create($data);
                })
                ->successRedirectUrl(route('filament.apolo.resources.hr.pagos.index')),
        ];
    }
}
