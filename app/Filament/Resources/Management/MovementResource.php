<?php

namespace App\Filament\Resources\Management;

use App\Filament\Resources\Management\MovementResource\Pages;
use App\Forms\Components\CustomerProyectField;
use App\Models\Management\Movement;
use App\Models\Management\Proyect;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\RawJs;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class MovementResource extends Resource
{

    protected static ?string $model = Movement::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'movimientos';

    protected static ?string $modelLabel = 'Movimientos';

    protected static ?string $pluralModelLabel = 'Ventas/Pagos';

    protected static ?string $recordTitleAttribute = 'fecha';

    protected static ?string $navigationGroup = 'Gestión';

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    public static function getRecordTitle(?Model $record): string | Htmlable | null
    {
        return 'movimiento [' . $record?->tipo . '] del ' . $record?->fecha;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([

                Grid::make(2)
                    ->schema([
                        CustomerProyectField::make('proyect_data')
                            ->relationship('proyect')
                            ->label(false)
                            ->columnSpanFull(),
                        // ->disabledOn(MovementsRelationManager::class),

                        Forms\Components\Section::make('Detalles')
                            ->columns(3)
                            ->schema([

                                Forms\Components\DatePicker::make('fecha')
                                    ->default(\now())
                                    ->required(),

                                Forms\Components\Placeholder::make('monto_proyecto')
                                    // ->readOnly()
                                    // ->prefix('$')
                                    ->live()
                                    // ->disabled(fn (Get $get) => is_null($get('proyect_data.id')) ? true : false)
                                    ->hintIcon('heroicon-s-information-circle')
                                    // ->hint('from Proyects')
                                    ->content(function (Get $get) {
                                        return !is_null($get('proyect_data.id')) ? '$' . number_format(
                                            Proyect::where('id', $get('proyect_data.id'))
                                                ->get()
                                                ->first()
                                                ->monto_proyectado,
                                            0,
                                            0,
                                            '.'
                                        )
                                            : '$0';
                                    }),
                                Forms\Components\Grid::make(3)
                                    ->live()
                                    ->columnSpan(3)
                                    ->schema([
                                        Forms\Components\ToggleButtons::make('tipo')
                                            ->required()
                                            ->inline()
                                            ->grouped()
                                            ->default('venta')
                                            ->options([
                                                'venta' => 'Venta',
                                                'pago' => 'Pago',
                                            ])
                                            ->icons([
                                                'venta' => 'heroicon-o-pencil',
                                                'pago' => 'heroicon-o-clock',
                                            ])
                                            ->colors([
                                                'venta' => 'info',
                                                'pago' => 'success',
                                            ])
                                            ->live()
                                            ->afterStateUpdated(fn(ToggleButtons $component) => $component
                                                ->getContainer()
                                                ->getComponent('dynamicMontosFields')
                                                ->getChildComponentContainer()
                                                ->fill()),

                                        Forms\Components\Grid::make(4)
                                            ->live()
                                            ->columnSpan(2)
                                            ->schema(fn(Get $get): array => match ($get('tipo')) {
                                                'venta' => [
                                                    Forms\Components\TextInput::make('cargo')
                                                        ->mask(RawJs::make(<<<'JS'
                                                                $money($input, ',', '.')
                                                            JS))
                                                        ->stripCharacters('.')
                                                        ->numeric()
                                                        ->prefix('$')
                                                        ->hintAction(
                                                            Action::make('Copiar monto proyectado')
                                                                ->icon('heroicon-s-document-duplicate')
                                                                ->action(function (Set $set, Get $get, $state) {
                                                                    $set('cargo', Proyect::where('id', $get('proyect_data.id'))
                                                                        ->get()
                                                                        ->first()
                                                                        ->monto_proyectado);
                                                                })
                                                        )
                                                        ->columnSpanFull(),
                                                    Forms\Components\Hidden::make('ingreso'),
                                                ],
                                                'pago' => [
                                                    Forms\Components\Hidden::make('cargo'),
                                                    Forms\Components\TextInput::make('ingreso')
                                                        ->mask(RawJs::make('$money($input)'))
                                                        ->stripCharacters('.')
                                                        ->numeric()
                                                        ->prefix('$')
                                                        ->hintAction(
                                                            Action::make('Copiar monto proyectado')
                                                                ->icon('heroicon-s-document-duplicate')
                                                                ->action(function (Set $set, Get $get, $state) {
                                                                    $set('ingreso', Proyect::where('id', $get('proyect_data.id'))
                                                                        ->get()
                                                                        ->first()
                                                                        ->monto_proyectado);
                                                                })
                                                        )
                                                        ->columnSpanFull(),
                                                ],
                                                default => []
                                            })
                                            ->key('dynamicMontosFields'),
                                    ]),
                                Forms\Components\TextInput::make('cot')
                                    ->required(),

                                Forms\Components\Select::make('tipo_factura')
                                    ->required()
                                    ->options([
                                        'numero' => 'Numero factura',
                                        'SF' => 'Sin factura',
                                        'PEND' => 'Factura pendiente',
                                    ])
                                    ->live(),

                                Forms\Components\TextInput::make('factura')
                                    ->maxLength(10)
                                    ->label('Nro. factura')
                                    ->live()
                                    ->disabled(fn(Get $get) => $get('tipo_factura') != 'numero'),

                                Forms\Components\Textarea::make('detalle')
                                    // ->required()
                                    ->columnSpanFull(),

                            ]),
                        Forms\Components\Section::make('Anexos')
                            ->columns(2)
                            ->schema([

                                SpatieMediaLibraryFileUpload::make('movement_files')
                                    ->label('Archivos')
                                    ->collection('movimientos')
                                    ->multiple()
                                    ->openable()
                                    ->downloadable()
                                    ->deletable()
                                    ->previewable()
                                    ->columnSpanFull(),
                                Forms\Components\RichEditor::make('observaciones')
                                    ->maxLength(65535)
                                    ->disableAllToolbarButtons()
                                    ->toolbarButtons(['bold', 'bulletList', 'italic', 'link', 'orderedList', 'redo', 'strike', 'undo'])
                                    ->columnSpanFull(),

                            ])
                    ])->columnSpan(['lg' => fn(?Model $record) => $record === null ? 3 : 2]),

                Forms\Components\Grid::make(1)
                    ->columnSpan(1)
                    ->schema([
                        Forms\Components\Section::make('Metadatos')
                            ->description('Información de los datos')
                            ->schema([
                                Forms\Components\Placeholder::make('created_at')
                                    ->label('Creado')
                                    ->content(fn(Model $record): ?string => $record->created_at?->diffForHumans()),

                                Forms\Components\Placeholder::make('updated_at')
                                    ->label('Última modificación')
                                    ->content(fn(Model $record): ?string => $record->updated_at?->diffForHumans()),
                            ])
                            ->columnSpan(['lg' => 1]),

                        Forms\Components\Section::make('Estado del proyecto')
                            ->schema([
                                Forms\Components\Toggle::make('estado')
                                    ->label('Pagado')
                                    ->inline()
                                    ->inlineLabel()
                                    ->required(),
                            ])
                            ->columnSpan(['lg' => 1]),
                    ])
                    ->hidden(fn(?Model $record) => $record === null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordClasses(fn(Model $record) => match ($record->tipo) {
                'venta' => 'venta !bg-yellow-100 !border-l-4 !border-l-yellow-400 dark:!bg-transparent !border-l-yellow-500 ',
                'pago' => 'pago !bg-lime-200  !border-l-4 !border-l-lime-500 dark:!bg-transparent !border-l-green-500 ',
                default => null,
            })
            ->paginationPageOptions([50, 100, 200, 'all'])
            ->defaultPaginationPageOption(50)
            ->defaultSort('fecha', 'desc')
            ->groups([
                Group::make('id_proyecto')
                    ->label('Proyectos')
                    ->titlePrefixedWithLabel(false)
                    ->orderQueryUsing(fn(Builder $query) => $query->orderBy('id_proyecto', 'desc'))
                    ->getTitleFromRecordUsing(fn(Movement $record): string => ucfirst($record->proyect->customer->nombre . '/' . $record->proyect->titulo)),
                'factura',
            ])
            ->columns([
                Tables\Columns\TextColumn::make('tipo')
                    ->formatStateUsing(function (string $state) {
                        return strtoupper($state);
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'venta' => 'warning',
                        'pago' => 'success',
                    })
                    ->searchable()
                    ->sortable()
                    ->columnSpan(1),

                Tables\Columns\TextColumn::make('fecha')
                    ->date()
                    ->sortable()
                    ->searchable()
                    ->columnSpan(1),

                Tables\Columns\TextColumn::make('proyect.customer.nombre')
                    ->label('Cliente')
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 15) {
                            return null;
                        }
                        return $state;
                    })
                    ->limit(15)
                    ->sortable()
                    ->searchable()
                    ->columnSpan(1),

                Tables\Columns\TextColumn::make('proyect.titulo')
                    ->label('Proyecto')
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 15) {
                            return null;
                        }
                        return $state;
                    })
                    ->limit(15)
                    ->sortable()
                    ->searchable()
                    ->columnSpan(1),

                Tables\Columns\TextColumn::make('cot')
                    ->label('Cotizacion')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(function (string $state) {
                        if ($state == 'SC') {
                            return 'Sin cotización.';
                        } elseif (count(explode('-', $state)) == 2) {
                            return 'COT-' . $state;
                        }
                        return $state;
                    })
                    ->sortable()
                    ->searchable()
                    ->columnSpan(1),

                Tables\Columns\TextColumn::make('factura')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->searchable()
                    ->columnSpan(1),

                Tables\Columns\TextColumn::make('monto_proyecto')
                    ->label('Monto proyectado')
                    ->currency('clp')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->columnSpan(1),

                Tables\Columns\TextColumn::make('cargo')
                    ->numeric()
                    ->placeholder('$0')
                    ->currency('clp')
                    ->summarize(Sum::make()->label('Cargos'))
                    ->sortable()
                    ->searchable()
                    ->columnSpan(1),

                Tables\Columns\TextColumn::make('ingreso')
                    ->numeric()
                    ->placeholder('$0')
                    ->currency('clp')
                    ->summarize(Sum::make()->label('Ingresos'))
                    ->sortable()
                    ->searchable()
                    ->columnSpan(1),

                // Tables\Columns\ToggleColumn::make('estado')
                //     ->label('Pagado')
                //     ->sortable()
                //     ->searchable()
                //     //   ->badge()
                //     ->placeholder('No pago')
                //   ->color(fn (string $state): string => match ($state) {
                //     null => 'gray',
                //     '1' => 'success',
                //   })
                //   ->formatStateUsing(function (string $state) {
                //   return $state;
                //     if ($state) {
                //       return 'PAGADO';
                //     } elseif ($state === \null) {
                //       return "";
                //     }
                //   })
                //     ->columnSpan(1),
            ])
            ->filters([
                DateRangeFilter::make('created_at')
                    ->placeholder('desde - hasta')
                    ->label('Filtrar por fecha'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    // Tables\Actions\Action::make('pagar_proyect')
                    //     // ->hidden(fn (Model $record): bool => ($record->id_proyecto > 0) ? true : false)
                    //     ->label('Pagar proyecto relacionado')
                    //     ->color('info')
                    //     ->icon('heroicon-o-archive-box-arrow-down')
                    //     ->modalHeading(fn(Model $record): string => 'Cambias estado a "' . $record->proyect->titulo . '" de ' . $record->proyect->customer->nombre)
                    //     ->modalSubmitActionLabel('Guardar')
                    //     ->modalFooterActionsAlignment('right')
                    //     ->modalWidth('md')
                    //     ->requiresConfirmation()
                    //     ->action(function (array $data, Model $record): void {
                    //         $proyect = Proyect::find($record->id_proyecto);
                    //         $proyect->estado = 1;
                    //         $proyect->save();
                    //     })
                    //     ->form(function (?Model $record): array {
                    //         $relatedMovements = Movement::where('id_proyecto', '=', $record->id_proyecto)->get();
                    //         $relatedCargos = $relatedMovements->sum('cargo');
                    //         $relatedIngresos = $relatedMovements->sum('ingreso');
                    //         $diff = $relatedCargos - $relatedIngresos;
                    //         $countPagos = $relatedMovements->where('tipo', 'pago')->count();
                    //         $color = $countPagos > 0 ? 'warning' : 'danger';
                    //         $mensaje = $countPagos > 0 ? 'Verifica que no exista deuda antes de cambiar el estado del proyecto.' : '¡El proyecto no registra pagos! Verifica que no exista deuda antes de cambiar el estado del proyecto.';
                    //         // $countVentas = $relatedMovements->where('tipo', 'venta')->count();
                    //         // dd($countVentas);
                    //         return [
                    //             Forms\Components\Grid::make(3)
                    //                 ->schema([
                    //                     Forms\Components\Placeholder::make('cargos')
                    //                         ->content(number_format($relatedCargos, 0, 0, '.')),
                    //                     Forms\Components\Placeholder::make('Ingresos')
                    //                         ->content(number_format($relatedIngresos, 0, 0, '.')),
                    //                     Forms\Components\Placeholder::make('diferencia')
                    //                         ->content(number_format($diff, 0, 0, '.')),
                    //                 ]),
                    //             Forms\Components\Placeholder::make('aviso')
                    //                 ->hintColor($color)
                    //                 ->hint($mensaje),
                    //         ];
                    //     }),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ])->dropdown(false),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMovements::route('/'),
            'create' => Pages\CreateMovement::route('/create'),
            'edit' => Pages\EditMovement::route('/{record}/edit'),
        ];
    }
    public static function getWidgets(): array
    {
        return [
            MovementResource\Widgets\MovimientosMesWidget::class,
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
