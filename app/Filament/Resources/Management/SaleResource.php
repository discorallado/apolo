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
use Filament\Infolists\Components\Actions\Action as ActionsAction;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
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
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use Pelmered\FilamentMoneyField\Infolists\Components\MoneyEntry;

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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(3)
            ->schema([
                Grid::make(1)
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Detalles')
                            ->columns(2)
                            ->icon('heroicon-o-arrows-right-left')
                            ->description('Detalles del movimiento')
                            ->schema([
                                TextEntry::make('proyect.customer.nombre')
                                    ->icon('heroicon-s-user')
                                    ->columnSpan(1)
                                    ->formatStateUsing(fn(string $state): string => strtoupper($state))
                                    ->label('Cliente')
                                    ->hintAction(
                                        ActionsAction::make('Ver cliente')
                                            ->url(fn(?Model $record) => route('filament.apolo.resources.clientes.view', ['record' => $record->proyect->customer->id]))
                                            ->openUrlInNewTab()
                                            ->icon('heroicon-o-link')
                                    ),
                                TextEntry::make('proyect.titulo')
                                    ->icon('heroicon-s-rectangle-stack')
                                    ->label('Proyecto')
                                    ->formatStateUsing(fn(string $state): string => strtoupper($state))
                                    ->hintAction(
                                        ActionsAction::make('Ver proyecto')
                                            ->url(fn(?Model $record) => route('filament.apolo.resources.proyectos.view', ['record' => $record->proyect->id]))
                                            ->openUrlInNewTab()
                                            ->icon('heroicon-o-link')
                                    ),
                            ]),


                        Section::make('Detalles')
                            ->icon('heroicon-o-document-magnifying-glass')
                            ->description('Datos de identificación')
                            ->columns(2)
                            ->columnSpan(2)
                            ->schema([
                                TextEntry::make('folio')
                                    ->icon('heroicon-s-hashtag')
                                    ->placeholder('sin registro.')
                                    ->prefix('N° ')
                                    ->columnSpan(1),
                                TextEntry::make('fecha_dcto')
                                    ->icon('heroicon-s-calendar-days')
                                    ->placeholder('sin registro.')
                                    ->columnSpan(1),
                                TextEntry::make('DTO')
                                    ->label('Tipo documento')
                                    ->icon('heroicon-s-document')
                                    ->placeholder('sin registro.')
                                    ->columnSpan(1),

                            ]),

                        Section::make('Monto')
                            ->icon('heroicon-o-document-magnifying-glass')
                            ->columns(4)
                            ->columnSpan(2)
                            ->schema([
                                MoneyEntry::make('neto')
                                    ->columnSpan(1),
                                MoneyEntry::make('iva')
                                    ->placeholder('sin registro.')
                                    ->columnSpan(1),
                                MoneyEntry::make('excento')
                                    ->placeholder('sin registro.')
                                    ->columnSpan(1),
                                MoneyEntry::make('total')
                                    ->placeholder('sin registro.')
                                    ->columnSpan(1),
                            ]),

                        // Section::make('Archivos')
                        //     ->icon('heroicon-s-paper-clip')
                        //     // ->description('Documentos adjuntos.')
                        //     ->schema([
                        //         ViewEntry::make('purchase_files')
                        //             ->label(false)
                        //             ->view('infolists.components.files-entry')
                        //             ->state(fn(Model $record) => $record->getMedia('compras'))
                        //             ->columnSpanFull(),
                        //     ])
                        //     ->columns(3)
                        //     ->columnSpan(2),
                    ]),
                Grid::make(1)
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Información del registro')
                            ->icon('heroicon-s-information-circle')
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Creado')
                                    ->since(),
                                TextEntry::make('updated_at')
                                    ->label('Última modificación')
                                    ->since()
                                    ->placeholder('sin modificaciones.'),
                                TextEntry::make('user.name')
                                    ->formatStateUsing(fn(string $state): string => strtoupper($state))
                                    ->icon('heroicon-s-user'),
                            ])
                    ]),
            ]);
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
                            // ->description('Indique los detalles del dcto. de compra.')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('folio')
                                    ->columnSpan(1)
                                    ->required()
                                    ->prefixIcon('heroicon-s-hashtag')
                                    ->maxLength(50),
                                Forms\Components\DatePicker::make('fecha_dcto')
                                    ->columnSpan(1)
                                    ->prefixIcon('heroicon-s-calendar-days')
                                    ->default(now())
                                    ->required(),
                                Forms\Components\Select::make('tipo_doc')
                                    ->prefixIcon('heroicon-s-document')
                                    ->columnSpan(2)
                                    ->searchable()
                                    ->options(collect(app(GeneralSettings::class)->codigos_dt)->pluck('label', 'code'))
                                    ->required(),
                            ]),

                        Forms\Components\Section::make('Montos')
                            ->icon('heroicon-s-currency-dollar')
                            // ->description('Indique solo el monto exento y el neto de la venta.')
                            ->columns(2)
                            ->schema([
                                MoneyInput::make('exento')
                                    ->required()
                                    ->columnSpan(1)
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                        $set('iva', round((int)$get('neto') * 0.19, 0));
                                        $set('total',  round((int)$get('neto') + (int)$get('neto') * 0.19 + (int)$state, 0));
                                    }),
                                MoneyInput::make('iva')
                                    ->readOnly()
                                    ->columnSpan(1),
                                MoneyInput::make('neto')
                                    ->required()
                                    ->columnSpan(1)
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                        $set('iva', round((int)$state * 0.19, 0));
                                        $set('total', (int)$state + round((int)$state * 0.19, 0) + (int)$get('exento'));
                                    }),
                                MoneyInput::make('total')
                                    ->readOnly()
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpan(['lg' => fn(?Sale $record) => $record === null ? 3 : 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Metadatos')
                            // ->description('Información de los datos')
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
                    ->columnSpan(1)
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_doc')
                    ->label('Docto')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->columnSpan(1)
                    ->color(fn(string $state): string => match ($state) {
                        default => 'primary',
                        '33' => 'success',
                        '34' => 'info',
                        '61' => 'warning',
                    })
                    ->formatStateUsing(function (Sale $docto, $state) {
                        $arreglo = collect(app(GeneralSettings::class)->codigos_dt)->pluck('min', 'code');
                        return $arreglo[$state] . ' N°' . $docto->folio;
                    }),

                Tables\Columns\TextColumn::make('customer.nombre')
                    ->label('Cliente')
                    ->icon('heroicon-o-rectangle-stack')
                    ->placeholder('Sin asignar.')
                    ->iconColor('gray')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('proyect.titulo')
                    ->label('Proyecto')
                    ->placeholder('Sin asignar.')
                    ->icon('heroicon-o-users')
                    ->iconColor('gray')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->summarize(Sum::make()->money('clp', 1, 'es_CL')->label('Total'))
                    ->numeric()
                    ->currency('CLP')
                    ->columnSpan(1)
                    ->searchable()
                    ->sortable(),

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

            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
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
            'index' => Pages\ManageSales::route('/'),
            // 'create' => Pages\CreateSale::route('/create'),
            // 'edit' => Pages\EditSale::route('/{record}/edit'),
            'view' => Pages\ViewSale::route('/{record}'),
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