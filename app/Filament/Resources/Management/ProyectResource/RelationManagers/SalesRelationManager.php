<?php

namespace App\Filament\Resources\Management\ProyectResource\RelationManagers;

use App\Filament\Resources\Management\SaleResource;
use App\Models\Management\Sale;
use App\Settings\GeneralSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalesRelationManager extends RelationManager
{
    protected static ?string $title = 'Ventas';

    protected static ?string $label = 'Venta';

    protected static string $relationship = 'Sales';

    public function form(Form $form): Form
    {
        return SaleResource::form($form);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('folio')
            ->inverseRelationship('proyect')
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('fecha_dcto')
                    ->label('Fecha')
                    ->date()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_doc')
                    ->label('Documento')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->alignCenter()
                    ->color(fn(string $state): string => match ($state) {
                        default => 'primary',
                        '33' => 'success',
                        '34' => 'info',
                        '61' => 'warning',
                    })
                    ->formatStateUsing(function (Sale $docto, $state) {
                        $arreglo = collect(app(GeneralSettings::class)->codigos_dt)->pluck('min', 'code');
                        return $arreglo[$state] . ' #' . $docto->folio;
                    }),


                Tables\Columns\TextColumn::make('proyect.customer.nombre')
                    ->label('Cliente')
                    ->icon('heroicon-o-users')
                    ->iconColor('gray')
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (\strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    })
                    ->copyable()
                    ->numeric()
                    ->limit(36)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->summarize(Sum::make()->label('Total')->money('clp'))
                    ->numeric()
                    ->currency('CLP')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                // Tables\Filters\TrashedFilter::make()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AssociateAction::make()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DissociateAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DissociateBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
