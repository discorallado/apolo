<?php

namespace App\Filament\Resources\Management;

use App\Filament\Resources\Management\ProyectResource\RelationManagers\MovementsRelationManager;
use App\Filament\Resources\Management\SaleResource\Pages;
use App\Forms\Components\CustomerProyectField;
use App\Models\Management\Proyect;
use App\Models\Management\Sale;
use App\Settings\GeneralSettings;
use App\Tables\Columns\ProyectAsignColumn;
use Carbon\Carbon;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class SaleResource extends Resource
{

    protected static ?string $model = Sale::class;

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'ventas';

    protected static ?string $modelLabel = 'Venta';

    protected static ?string $pluralModelLabel = 'Ventas';

    protected static ?string $recordTitleAttribute = 'folio';

    protected static ?string $navigationGroup = 'Gestión';

    // protected static ?string $navigationParentItem = 'Ventas/Pagos';

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    public static function getRecordTitl(?Model $record): string|Htmlable|null
    {
        return 'venta #' . $record?->folio . ' del ' . $record?->fecha_dcto;
    }

    public static function form(Form $form): Form
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
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginationPageOptions([50, 100, 200, 'all'])
            ->defaultPaginationPageOption(50)
            ->defaultSort('fecha_dcto', 'desc')
            // ->defaultGroup('proyect.titulo')
            ->striped()
            ->groups([
                // 'proyect.titulo',
                Group::make('proyect.titulo')
                    ->label('Proyectos')
                    ->titlePrefixedWithLabel(false)
                    ->orderQueryUsing(fn(Builder $query) => $query->orderBy('id_proyecto', 'desc'))
                    ->getTitleFromRecordUsing(function (?Model $record) {
                        return ($record->proyect ? $record->proyect->customer->nombre . '/' . $record->proyect->titulo : 'Sin proyecto');
                    }),
            ])
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


                Tables\Columns\TextColumn::make('customer.nombre')
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


                ProyectAsignColumn::make('id_proyecto')
                    ->label('Proyecto'),


                Tables\Columns\TextColumn::make('created_at')
                    ->searchable()
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('desde:')
                            ->placeholder(fn($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('hasta:')
                            ->placeholder(fn($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Order from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Order until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Action::make('asignar_proyecto')
                        ->hidden(fn(Sale $record): bool => ($record->id_proyecto > 0) ? true : false)
                        ->label('Asignar proyecto')
                        ->color('info')
                        ->icon('heroicon-o-archive-box-arrow-down')
                        ->modalHeading('Asignar proyecto')
                        ->modalSubheading(fn(Sale $record): string => 'Asigne un proyecto a la venta del ' . $record->fecha_dcto . ' para ' . $record->customer->nombre)
                        ->modalButton('Guardar')
                        ->modalFooterActionsAlignment('right')
                        ->modalWidth('md')
                        ->action(function (array $data, Sale $record): void {
                            $record->id_proyecto = $data['id_proyecto'];
                            $record->save();
                        })
                        ->form([
                            Forms\Components\Select::make('id_proyecto')
                                ->label('Proyecto')
                                ->options(function (Model $record) {
                                    return Proyect::where('id_cliente', '=', $record->customer->id)->pluck('titulo', 'id');
                                })
                                ->searchable(),
                        ]),
                    Action::make('quitar_proyecto')
                        ->hidden(fn(Sale $record): bool => ($record->id_proyecto === null) ? true : false)
                        ->label('Quitar proyecto')
                        ->color('warning')
                        ->icon('heroicon-o-archive-box-x-mark')
                        ->action(function (Sale $record): void {
                            $record->id_proyecto = null;
                            $record->save();
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        return (string) $modelClass::all()->count();
    }
}
