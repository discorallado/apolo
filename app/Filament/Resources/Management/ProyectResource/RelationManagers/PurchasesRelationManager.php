<?php

namespace App\Filament\Resources\Management\ProyectResource\RelationManagers;

use App\Filament\Resources\Management\PurchaseResource;
use App\Models\Management\Purchase;
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

class PurchasesRelationManager extends RelationManager
{
    protected static ?string $title = 'Compras';

    protected static ?string $label = 'Compra';

    protected static string $relationship = 'Purchases';

    public function form(Form $form): Form
    {
        return PurchaseResource::form($form);
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
                    // ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->searchable()
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_doc')
                    ->label('Documento')
                    ->badge()
                    ->sortable()
                    ->searchable()
                    // ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->color(fn(string $state): string => match ($state) {
                        '45' => 'success',
                        '60' => 'warning',
                    })
                    ->formatStateUsing(function (Purchase $docto, $state) {
                        $arreglo = collect(app(GeneralSettings::class)->codigos_dt)->pluck('min', 'code');
                        return $arreglo[$state] . ' #' . $docto->folio;
                    }),


                Tables\Columns\TextColumn::make('supplier.nombre')
                    ->label('Proveedor')
                    ->numeric()
                    ->icon('heroicon-o-building-storefront')
                    ->iconColor('gray')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->numeric()
                    ->currency('CLP')
                    ->searchable()
                    ->summarize(Sum::make()->label('Total')->money('clp'))
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