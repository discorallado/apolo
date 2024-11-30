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
use Filament\Forms\Components\Grid;
use IbrahimBougaoua\FilaProgress\Tables\Columns\ProgressBar;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;

// use Filament\Forms\Components\TextInput;

class TablaWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Días trabajados';

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
                ->toggleable(isToggledHiddenByDefault: true)
                ->sortable()
                ->sortable(),
            Tables\Columns\TextColumn::make('proyect.titulo')
                ->label('Proyecto')
                ->toggleable(isToggledHiddenByDefault: true)
                ->sortable()
                ->sortable(),
            Tables\Columns\TextColumn::make('custom')
                ->label('Título')
                // ->toggleable(isToggledHiddenByDefault: true)
                ->sortable()
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
            MoneyColumn::make('total_dias')
                ->label('Cargo')
                // ->money('CLP', 0, 'cl')
                ->placeholder('Sin cargo'),
            MoneyColumn::make('payments_sum_monto')
                ->sum('payments', 'monto')
                ->label('Pagos')
                // ->money('CLP', 0, 'cl')
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
                ->modalHeading(fn(Model $record): string => 'Pagar a ' . $record->custom)
                ->modalSubmitActionLabel('Guardar')
                ->modalFooterActionsAlignment('right')
                ->modalWidth('lg')
                ->fillForm(fn(Binnacle $record): array => [
                    'custom' => $record->custom,
                    'total_dias' => $record->total_dias,
                    'monto' => ($record->total_dias - $record->payments->sum('monto')),
                ])
                ->form(function (?Model $record): array {
                    return [
                        Grid::make(3)
                            ->schema([
                                TextInput::make('custom')
                                    ->label('Asignado a')
                                    ->prefixIcon('heroicon-s-tag')
                                    ->disabled()
                                    // ->readOnly()
                                    ->columnSpanFull(),
                                MoneyInput::make('valor_dia'),
                                MoneyInput::make('dias'),
                                MoneyInput::make('total_dias')
                                    ->label('Total'),
                                MoneyInput::make('monto')
                                    ->label('Cargo')
                                    ->hint('por días trabajados')
                                    ->columns(2),
                                FileUpload::make('attachment')
                                    ->directory('payments-attachments')
                                    ->columnSpanFull(),
                            ]),
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
