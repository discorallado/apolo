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
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Freshwork\ChileanBundle\Rut;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use Pelmered\FilamentMoneyField\Infolists\Components\MoneyEntry;

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
                                        Action::make('Ver cliente')
                                            ->url(fn(?Model $record) => route('filament.apolo.resources.clientes.view', ['record' => $record->proyect->customer->id]))
                                            ->openUrlInNewTab()
                                            ->icon('heroicon-o-link')
                                    ),
                                TextEntry::make('proyect.titulo')
                                    ->icon('heroicon-s-rectangle-stack')
                                    ->label('Proyecto')
                                    ->formatStateUsing(fn(string $state): string => strtoupper($state))
                                    ->hintAction(
                                        Action::make('Ver proyecto')
                                            ->url(fn(?Model $record) => route('filament.apolo.resources.proyectos.view', ['record' => $record->proyect->id]))
                                            ->openUrlInNewTab()
                                            ->icon('heroicon-o-link')
                                    ),

                                // Grid::make(3)
                                //     ->schema([
                                //         TextEntry::make('fecha')
                                //             ->date()
                                //             ->columnSpan(1),
                                //         TextEntry::make('cot')
                                //             ->label('Cotización')
                                //             ->placeholder('Sin cotización.')
                                //             ->prefix('COT-'),
                                //         TextEntry::make('factura')
                                //             ->label('Nro. factura')
                                //             ->placeholder('Sin factura.')
                                //             ->prefix('N° '),
                                //     ]),
                                // TextEntry::make('detalle')
                                //     ->placeholder('Sin detalles.')
                                //     ->columnSpanFull(),
                            ]),


                        Section::make('Detalles')
                            ->icon('heroicon-o-document-magnifying-glass')
                            ->description('Datos de identificación')
                            ->columns(2)
                            ->columnSpan(2)
                            ->schema([
                                TextEntry::make('supplier.nombre')
                                    ->icon('heroicon-s-users')
                                    ->placeholder('sin registro.')
                                    ->columnSpan(1),
                                TextEntry::make('fecha_dcto')
                                    ->icon('heroicon-s-calendar-days')
                                    ->placeholder('sin registro.')
                                    ->columnSpan(1),
                                TextEntry::make('DTO')
                                    ->label('Tipo documento')
                                    ->icon('heroicon-s-document-chart-bar')
                                    ->label('Tipo')
                                    ->columnSpan(1),
                                TextEntry::make('folio')
                                    ->icon('heroicon-s-hashtag')
                                    ->placeholder('sin registro.')
                                    ->prefix('N° ')
                                    ->columnSpan(1),
                            ]),

                        Section::make('Monto')
                            ->icon('heroicon-o-document-magnifying-glass')
                            ->columns(3)
                            ->columnSpan(2)
                            ->schema([
                                MoneyEntry::make('neto')
                                    ->columnSpan(1),
                                MoneyEntry::make('iva')
                                    ->placeholder('sin registro.')
                                    ->columnSpan(1),
                                MoneyEntry::make('total')
                                    ->placeholder('sin registro.')
                                    ->columnSpan(1),
                            ]),

                        Section::make('Archivos')
                            ->icon('heroicon-s-paper-clip')
                            // ->description('Documentos adjuntos.')
                            ->schema([
                                ViewEntry::make('purchase_files')
                                    ->label(false)
                                    ->view('infolists.components.files-entry')
                                    ->state(fn(Model $record) => $record->getMedia('compras'))
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->columnSpan(2),
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
                            // ->description('Indique los detalles del dcto. de compra.')
                            ->columns(2)
                            ->schema([
                                Forms\Components\Select::make('id_proveedor')
                                    ->label('Proveedor')
                                    ->columnSpan(1)
                                    ->searchable()
                                    ->prefixIcon('heroicon-s-users')
                                    ->options(Supplier::query()->pluck('nombre', 'id'))
                                    ->required(),
                                Forms\Components\DatePicker::make('fecha_dcto')
                                    ->label('Fecha documento')
                                    ->prefixIcon('heroicon-o-calendar-days')
                                    ->default(now())
                                    ->columnSpan(1)
                                    ->required(),
                                Forms\Components\Select::make('tipo_doc')
                                    ->label('Tipo de documento')
                                    ->columnSpan(1)
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
                            ]),
                        Forms\Components\Section::make('Montos')
                            ->icon('heroicon-s-currency-dollar')
                            // ->description('Indique solo el monto de la compra')
                            ->columns(3)
                            ->schema([
                                MoneyInput::make('neto')
                                    ->columnSpan(1)
                                    ->prefix('$')
                                    ->readOnly()
                                    ->numeric(),
                                MoneyInput::make('iva')
                                    ->prefix('$')
                                    ->columnSpan(1)
                                    ->readOnly()
                                    ->numeric(),
                                MoneyInput::make('total')
                                    ->prefix('$')
                                    ->columnSpan(1)
                                    ->required()
                                    ->numeric()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        // $set('neto', round((str_replace('.', '', $state) / 1.19), 0));
                                        // $set('iva', str_replace('.', '', $state));
                                        $set('neto', round(((int)str_replace('.', '', $state) / 1.19), 0));
                                        $set('iva', round((((int)str_replace('.', '', $state) / 1.19) * 0.19), 0));
                                    }),
                            ]),
                        Forms\Components\Section::make('Archivos')
                            ->icon('heroicon-o-paper-clip')
                            ->collapsed()
                            // ->description('Archivos adjuntos.')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('purchase_files')
                                    ->label(false)
                                    ->collection('compras')
                                    ->multiple()
                                    ->openable()
                                    ->downloadable()
                                    ->deletable()
                                    ->previewable()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(3),
                // ->columnSpan(['lg' => fn(?Purchase $record) => $record === null ? 3 : 2]),


                // Forms\Components\Group::make()
                //     ->schema([
                //         Forms\Components\Section::make('Metadatos')
                //             // ->description('Información de los datos')
                //             ->icon('heroicon-s-information-circle')
                //             ->schema([
                //                 Forms\Components\Placeholder::make('created_at')
                //                     ->content(function (Purchase $record) {
                //                         return $record->created_at;
                //                     })
                //                     ->label('Creado'),
                //                 Forms\Components\Placeholder::make('updated_at')
                //                     ->content(function (Purchase $record) {
                //                         return $record->updated_at ?? 'Nunca.';
                //                     })
                //                     ->label('Última modificación'),
                //             ]),
                //     ])
                //     ->hidden(fn(?Purchase $record) => $record === null)
                //     ->columnSpan(['lg' => 1]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginationPageOptions([50, 100, 200, 'all'])
            ->defaultPaginationPageOption(50)
            ->defaultSort('fecha_dcto', 'desc')
            // ->defaultGroup('proyect.titulo')
            ->striped()
            // ->groups([
            //     Group::make('proyect.titulo_completo')
            //         ->label('Proyectos')
            //         ->titlePrefixedWithLabel(false)
            //         ->orderQueryUsing(fn(Builder $query) => $query->orderBy('id_proyecto', 'desc'))
            //         ->getTitleFromRecordUsing(function (?Model $record) {
            //             return ($record->proyect ? $record->proyect->customer->nombre . '/' . $record->proyect->titulo : 'Sin proyecto');
            //         }),
            // ])
            ->columns([
                Tables\Columns\TextColumn::make('fecha_dcto')
                    ->label('Fecha')
                    ->searchable()
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_doc')
                    ->label('Docto')
                    ->badge()
                    ->sortable()
                    ->searchable()
                    ->color(fn(string $state): string => match ($state) {
                        default => 'primary',
                        '45' => 'success',
                        '46' => 'success',
                        '60' => 'warning',
                    })
                    ->formatStateUsing(function (Purchase $record, $state) {
                        $arreglo = collect(app(GeneralSettings::class)->codigos_dt)->pluck('min', 'code');
                        return $arreglo[$state] . ' N° ' . $record->folio;
                    }),
                Tables\Columns\TextColumn::make('supplier.nombre')
                    // ->size(TextColumn\TextColumnSize::Small)
                    ->label('Proveedor')
                    ->limit(20)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 20) {
                            return null;
                        }
                        return $state;
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.nombre')
                    ->label('Cliente')
                    ->placeholder('Sin asignar.')
                    ->state(function (?Model $record) {
                        if ($record->proyect) {
                            return $record->proyect->customer->nombre;
                        }
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('proyect.titulo')
                    ->label('Proyecto')
                    ->placeholder('Sin asignar.')
                    ->limit(20)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 20) {
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
                    ->summarize(Sum::make()->money('clp', 1, 'es_CL')->label('Total'))
                    ->sortable(),

                // ProyectAsignColumn::make('proyect.titulo')
                //     ->label('Proyecto'),

                Tables\Columns\IconColumn::make('proyecto')
                    ->label(false)
                    ->boolean()
                    ->alignCenter()
                    ->state(function (Model $record): bool {
                        return ($record->proyect) ? true : false;
                    })
                    ->disabled(function (Model $record): bool {
                        return ($record->proyect) ? false : true;
                    })
                    ->tooltip('Asigar a entidad')
                    ->trueIcon(false)
                    ->falseIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('success')
                    ->falseColor('warning'),

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
                    // Tables\Actions\Action::make('asignar_proyecto')
                    //     ->hidden(fn(Model $record): bool => ($record->id_proyecto > 0) ? true : false)
                    //     ->label('Asignar proyecto')
                    //     ->color('info')
                    //     ->icon('heroicon-o-archive-box-arrow-down')
                    //     ->modalHeading('Asignar proyecto')
                    //     ->modalDescription(fn(Purchase $record): string => 'Asigne un proyecto a la venta del ' . $record->fecha_dcto . ' de ' . $record->supplier->nombre)
                    //     ->modalSubmitActionLabel('Guardar')
                    //     ->modalFooterActionsAlignment('right')
                    //     ->modalWidth('md')
                    //     ->action(function (array $data, Purchase $record): void {
                    //         $record->id_proyecto = $data['id_proyecto'];
                    //         $record->save();
                    //     })
                    //     ->form([
                    //         Forms\Components\Select::make('id_cliente')
                    //             ->label('Cliente')
                    //             ->options(Customer::query()->pluck('nombre', 'id'))
                    //             ->searchable()
                    //             ->live(),

                    //         Forms\Components\Select::make('id_proyecto')
                    //             ->label('Proyecto')
                    //             ->searchable()
                    //             ->options(fn(Get $get): Collection => Proyect::query()
                    //                 ->where('id_cliente', $get('id_cliente'))
                    //                 ->pluck('titulo', 'id'))
                    //     ]),

                    // Tables\Actions\Action::make('quitar_proyecto')
                    //     ->hidden(fn(Model $record): bool => ($record->id_proyecto === null) ? true : false)
                    //     ->label('Quitar proyecto')
                    //     ->color('warning')
                    //     ->icon('heroicon-o-archive-box-x-mark')
                    //     ->action(function (Purchase $record): void {
                    //         $record->id_proyecto = null;
                    //         $record->save();
                    //     })
                    //     ->requiresConfirmation(),
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
            'index' => Pages\ManagePurchases::route('/'),
            'view' => Pages\ViewPurchase::route('/{record}'),
            // 'create' => Pages\CreatePurchase::route('/create'),
            // 'edit' => Pages\EditPurchase::route('/{record}/edit'),
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