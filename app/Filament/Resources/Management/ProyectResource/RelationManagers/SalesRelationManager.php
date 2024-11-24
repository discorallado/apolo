<?php

namespace App\Filament\Resources\Management\ProyectResource\RelationManagers;

use App\Filament\Resources\Management\SaleResource;
use App\Forms\Components\CustomerProyectField;
use App\Models\Management\Sale;
use App\Settings\GeneralSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalesRelationManager extends RelationManager
{
    protected static ?string $title = 'Ventas';

    protected static ?string $label = 'Venta';

    protected static string $relationship = 'Sales';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([

                        CustomerProyectField::make('proyect_data')
                            ->relationship('proyect')
                            ->label(false)
                            ->columnSpanFull()
                            ->hiddenOn(MovementsRelationManager::class),


                        Forms\Components\Section::make('Detalles')
                            ->icon('heroicon-o-document-magnifying-glass')
                            ->description('Indique los detalles del dcto. de compra.')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('folio')
                                    ->columnSpan(1)
                                    ->required()
                                    ->prefixIcon('heroicon-s-hashtag')
                                    ->maxLength(50),
                                Forms\Components\DatePicker::make('fecha_dcto')
                                    ->columnSpan(1)
                                    ->default(now())
                                    ->required(),
                                Forms\Components\Select::make('tipo_doc')
                                    ->columnSpan(2)
                                    ->searchable()
                                    ->options(collect(app(GeneralSettings::class)->codigos_dt)->pluck('label', 'code'))
                                    ->required(),
                            ]),

                        Forms\Components\Section::make('Montos')
                            ->icon('heroicon-s-currency-dollar')
                            ->description('Indique solo el monto exento y el neto de la venta.')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('exento')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required()
                                    ->columnSpan(1)
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                        $set('iva', round($get('neto') * 0.19, 0));
                                        $set('total',  round($get('neto') + $get('neto') * 0.19 + $state, 0));
                                    }),
                                Forms\Components\TextInput::make('iva')
                                    ->numeric()
                                    ->prefix('$')
                                    ->readOnly()
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('neto')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->columnSpan(1)
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                        $set('iva', round($state * 0.19, 0));
                                        $set('total', $state + round($state * 0.19, 0) + $get('exento'));
                                    }),
                                Forms\Components\TextInput::make('total')
                                    ->numeric()
                                    ->prefix('$')
                                    ->readOnly()
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpan(['lg' => fn(?Sale $record) => $record === null ? 3 : 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Metadatos')
                            ->description('Información de los datos')
                            ->icon('heroicon-s-information-circle')
                            ->schema([
                                Forms\Components\Placeholder::make('created_at')
                                    ->content(function (Sale $record) {
                                        return $record->created_at;
                                    })
                                    ->label('Creado:'),
                                Forms\Components\Placeholder::make('updated_at')
                                    ->content(function (Sale $record) {
                                        return $record->updated_at ?? 'Nunca.';
                                    })
                                    ->label('Última modificación:'),
                            ]),

                        Forms\Components\Section::make('Movimiento')
                            ->schema([
                                Forms\Components\Placeholder::make('id_movimiento')
                                    ->content(function (?Model $record) {
                                        return $record->movement ? $record->movement : "Sin asignar";
                                    })
                                    ->default('Sin asignar')
                                // ->label('Creado:'),
                            ])
                    ])
                    ->hidden(fn(?Sale $record) => $record === null)
                    ->columnSpan(['lg' => 1]),
            ]);
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
                    ->summarize(Sum::make()->money('clp', 1, 'es_CL')->label('Total'))
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