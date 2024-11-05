<?php

namespace App\Filament\Resources\Management;

use App\Filament\Resources\Management\ProyectResource\RelationManagers\MovementsRelationManager;
use App\Filament\Resources\Management\PurchaseResource\Pages;
use App\Filament\Resources\Management\PurchaseResource\RelationManagers;
use App\Forms\Components\CustomerProyectField;
use App\Models\Management\Customer;
use App\Models\Management\Movement;
use App\Models\Management\Proyect;
use App\Models\Management\Purchase;
use App\Models\Management\Supplier;
use App\Settings\GeneralSettings;
use App\Tables\Columns\ProyectAsignColumn;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'compras';

    protected static ?string $modelLabel = 'Compra';

    protected static ?string $pluralModelLabel = 'Compras';

    protected static ?string $recordTitleAttribute = 'fecha_dcto';

    protected static ?string $navigationGroup = 'Gestión';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        return 'compra #' . $record?->folio . ' del ' . $record?->fecha_dcto;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Group::make()
                    ->schema([

                        CustomerProyectField::make('proyect_data')
                            ->relationship('proyect')
                            // ->required()
                            ->label(false)
                            ->columnSpanFull()
                            ->hiddenOn(MovementsRelationManager::class),

                        Forms\Components\Section::make('Detalles')
                            ->icon('heroicon-o-document-magnifying-glass')
                            ->description('Indique los detalles del dcto. de compra.')
                            ->columns(2)
                            ->schema([
                                Forms\Components\Select::make('tipo_doc')
                                    ->label('Tipo de documento')
                                    ->columnSpan(2)
                                    ->searchable()
                                    ->prefixIcon('heroicon-s-document')
                                    ->options(collect(app(GeneralSettings::class)->codigos_dt)->pluck('label', 'code'))
                                    ->required(),

                                Forms\Components\TextInput::make('folio')
                                    ->label('N°/Folio documento')
                                    ->columnSpan(1)
                                    ->prefixIcon('heroicon-s-hashtag')
                                    ->required()
                                    ->maxLength(50),
                                Forms\Components\DatePicker::make('fecha_dcto')
                                    ->label('Fecha documento')
                                    ->default(now())
                                    ->columnSpan(1)
                                    ->required(),
                            ]),
                        Forms\Components\Section::make('Montos')
                            ->icon('heroicon-s-currency-dollar')
                            ->description('Indique solo el monto de la compra')
                            ->columns(3)
                            ->schema([
                                Forms\Components\TextInput::make('neto')
                                    ->columnSpan(1)
                                    ->prefix('$')
                                    ->readOnly()
                                    ->numeric(),
                                Forms\Components\TextInput::make('iva')
                                    ->prefix('$')
                                    ->columnSpan(1)
                                    ->readOnly()
                                    ->numeric(),
                                Forms\Components\TextInput::make('total')
                                    ->prefix('$')
                                    ->columnSpan(1)
                                    ->required()
                                    ->numeric()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $set('neto', round(($state / 1.19), 0));
                                        $set('iva', round((($state / 1.19) * 0.19), 0));
                                    }),
                            ]),
                        // Forms\Components\Hidden::make('periodo'),
                        // Forms\Components\Hidden::make('ano'),
                    ])
                    ->columnSpan(['lg' => fn(?Purchase $record) => $record === null ? 3 : 2]),


                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Metadatos')
                            ->description('Información de los datos')
                            ->icon('heroicon-s-information-circle')
                            ->schema([
                                Forms\Components\Placeholder::make('created_at')
                                    ->content(function (Purchase $record) {
                                        return $record->created_at;
                                    })
                                    ->label('Creado'),
                                Forms\Components\Placeholder::make('updated_at')
                                    ->content(function (Purchase $record) {
                                        return $record->updated_at ?? 'Nunca.';
                                    })
                                    ->label('Última modificación'),
                            ]),
                    ])
                    ->hidden(fn(?Purchase $record) => $record === null)
                    ->columnSpan(['lg' => 1]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginationPageOptions([50, 100, 200, 'all'])
            ->defaultPaginationPageOption(50)
            ->defaultSort('fecha_dcto', 'desc')
            ->defaultGroup('proyect.titulo')
            ->striped()
            ->groups([
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
                        default => 'primary',
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

                ProyectAsignColumn::make('proyect.titulo')
                    ->label('Proyecto'),

                // Tables\Columns\IconColumn::make('proyecto')
                //     ->getStateUsing(function (Model $record): bool {
                //         return ($record->id_proyecto > 0) ? true : false;
                //     })
                //     ->boolean()
                //     ->sortable()
                //     ->alignCenter()
                //     ->trueIcon('heroicon-o-document-check')
                //     ->falseIcon('heroicon-o-exclamation-triangle')
                //     ->trueColor('success')
                //     ->falseColor('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('asignar_proyecto')
                        ->hidden(fn(Model $record): bool => ($record->id_proyecto > 0) ? true : false)
                        ->label('Asignar proyecto')
                        ->color('info')
                        ->icon('heroicon-o-archive-box-arrow-down')
                        ->modalHeading('Asignar proyecto')
                        ->modalDescription(fn(Purchase $record): string => 'Asigne un proyecto a la venta del ' . $record->fecha_dcto . ' de ' . $record->supplier->nombre)
                        ->modalSubmitActionLabel('Guardar')
                        ->modalFooterActionsAlignment('right')
                        ->modalWidth('md')
                        ->action(function (array $data, Purchase $record): void {
                            $record->id_proyecto = $data['id_proyecto'];
                            $record->save();
                        })
                        ->form([
                            Forms\Components\Select::make('id_cliente')
                                ->label('Cliente')
                                ->options(Customer::query()->pluck('nombre', 'id'))
                                ->searchable()
                                ->live(),

                            Forms\Components\Select::make('id_proyecto')
                                ->label('Proyecto')
                                ->searchable()
                                ->options(fn(Get $get): Collection => Proyect::query()
                                    ->where('id_cliente', $get('id_cliente'))
                                    ->pluck('titulo', 'id'))
                        ]),

                    Tables\Actions\Action::make('quitar_proyecto')
                        ->hidden(fn(Model $record): bool => ($record->id_proyecto === null) ? true : false)
                        ->label('Quitar proyecto')
                        ->color('warning')
                        ->icon('heroicon-o-archive-box-x-mark')
                        ->action(function (Purchase $record): void {
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
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        return (string) $modelClass::all()->count();
    }
}
