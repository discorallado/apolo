<?php

namespace App\Filament\Resources\Management;

use Closure;

use App\Filament\Resources\Management\MovementResource\Pages;
use App\Forms\Components\CustomerProyectField;
use App\Models\Management\Customer;
use App\Models\Management\Movement;
use App\Models\Management\Proyect;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Actions\Action as ActionsAction;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Grid as ComponentsGrid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
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
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use Pelmered\FilamentMoneyField\Infolists\Components\MoneyEntry;

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
        return 'registro de ' . $record?->tipo . ' del ' . $record?->fecha;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(3)
            ->schema([
                ComponentsGrid::make(2)
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
                                ComponentsGrid::make(3)
                                    ->schema([
                                        TextEntry::make('fecha')
                                            ->icon('heroicon-s-calendar-days')
                                            ->date()
                                            ->columnSpan(1),
                                        TextEntry::make('cot')
                                            ->icon('heroicon-s-document-text')
                                            ->label('Cotización')
                                            ->placeholder('Sin cotización.')
                                            ->prefix('COT-'),
                                        TextEntry::make('factura')
                                            ->icon('heroicon-s-receipt-percent')
                                            ->label('Nro. factura')
                                            ->placeholder('Sin factura.')
                                            ->prefix('N° '),
                                    ]),
                                TextEntry::make('detalle')
                                    ->placeholder('Sin detalles.')
                                    ->html()
                                    ->columnSpanFull(),
                            ]),
                        Section::make('Contable')
                            ->icon('heroicon-o-banknotes')
                            ->description('Resumen de ventas y pagos.')
                            ->columns(3)
                            ->columnSpan(2)
                            ->schema([
                                TextEntry::make('tipo')
                                    ->badge()
                                    ->icon(fn(string $state): string => match ($state) {
                                        'VENTA' => 'heroicon-o-shopping-cart',
                                        'PAGO' => 'heroicon-o-wallet',
                                    })
                                    ->color(fn(string $state): string => match ($state) {
                                        'VENTA' => 'warning',
                                        'PAGO' => 'success',
                                    })
                                    ->iconColor(fn(string $state): string => match ($state) {
                                        'VENTA' => 'info',
                                        'PAGO' => 'success',
                                    }),
                                MoneyEntry::make('valor')
                                    ->state(fn(?Model $record) => $record->cargo ?? $record->ingreso)
                                    ->columnSpan(1),


                                Fieldset::make('Resumen del Proyecto')
                                    ->columns(4)
                                    ->schema([
                                        MoneyEntry::make('monto_proyectado')
                                            ->state(function (Model $record): float {
                                                return $record->proyect->monto_proyectado;
                                            }),
                                        MoneyEntry::make('cargos')
                                            ->state(function (Model $record): float {
                                                return $record->proyect->movements->sum('cargo');
                                            }),

                                        MoneyEntry::make('ingresos')
                                            ->state(function (Model $record): float {
                                                return $record->proyect->movements->sum('ingreso');
                                            }),
                                        MoneyEntry::make('deuda')
                                            ->state(function (Model $record): float {
                                                return $record->proyect->movements->sum('cargo') - $record->proyect->movements->sum('ingreso');
                                            }),
                                        ComponentsGrid::make(2)
                                            ->schema([
                                                TextEntry::make('sales')
                                                    ->label('Facturas emitidas')
                                                    ->state(function (Model $record): float {
                                                        return $record->proyect->sales->count();
                                                    })
                                                    ->suffix(fn($state): string => ($state == 1) ? ' factura' : ' facturas'),
                                                TextEntry::make('purchases')
                                                    ->label('Facturas de compras')
                                                    ->state(function (Model $record): float {
                                                        return $record->proyect->purchases->count();
                                                    })
                                                    ->suffix(fn($state): string => ($state == 1) ? ' factura' : ' facturas'),
                                            ])
                                    ]),
                            ]),

                        Section::make('Extras')
                            ->columns(2)
                            ->description('Información extra')
                            ->icon('heroicon-o-paper-clip')
                            ->schema([
                                ViewEntry::make('customer_files')
                                    ->label('Archivos adjuntos')
                                    ->view('infolists.components.files-entry')
                                    ->state(fn(Model $record) => $record->getMedia('movimientos'))
                                    ->columnSpanFull(),
                                TextEntry::make('observaciones')
                                    ->placeholder('Sin observaciones.')
                                    ->columnSpanFull(),
                            ])
                    ]),
                ComponentsGrid::make(1)
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
            ->columns(2)
            ->schema([
                CustomerProyectField::make('proyect_data')
                    ->relationship('proyect')
                    ->label(false)
                    ->columnSpanFull(),

                Forms\Components\Section::make('Detalles')
                    ->columns(2)
                    ->icon('heroicon-o-arrows-right-left')
                    // ->description('Detalles del movimiento')
                    ->schema([
                        Forms\Components\DatePicker::make('fecha')
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-s-calendar-days')
                            ->format('Y-m-d')
                            ->default(now())
                            ->required(),

                        Forms\Components\ToggleButtons::make('tipo')
                            ->required()
                            ->inline()
                            ->columnSpan(1)
                            ->default('VENTA')
                            ->options([
                                'VENTA' => 'Venta',
                                'PAGO' => 'Pago',
                            ])
                            ->icons([
                                'VENTA' => 'heroicon-o-pencil',
                                'PAGO' => 'heroicon-o-clock',
                            ])
                            ->colors([
                                'VENTA' => 'info',
                                'PAGO' => 'success',
                            ]),

                        Forms\Components\Grid::make(4)
                            ->columnSpanFull()
                            ->schema([
                                Placeholder::make('monto_proyectado')
                                    ->key('monto_proyectado')
                                    ->live()
                                    ->content(function (Get $get) {
                                        if ($get('proyect_data.id')) {
                                            return money(Proyect::where('id', $get('proyect_data.id'))
                                                ->get()
                                                ->first()
                                                ->monto_proyectado, 'clp');
                                        } else {
                                            return money(0, 'clp');
                                        }
                                    })
                                    ->hintActions([
                                        Action::make('Copiar')
                                            ->label(false)
                                            ->icon('heroicon-o-document-duplicate')
                                            ->tooltip('Copiar valor')
                                            ->action(function (Placeholder $component, Set $set) {
                                                $set('valor', $component->getContent()->getAmount());
                                            }),
                                    ]),
                                Placeholder::make('deuda')
                                    ->key('deuda')
                                    ->live()
                                    ->content(function (Get $get) {
                                        if ($get('proyect_data.id')) {
                                            $related = Movement::where('id_proyecto', $get('proyect_data.id'));
                                            return money($related->sum('cargo') - $related->sum('ingreso'), 'clp');
                                            # code...
                                        } else {
                                            return money(0, 'clp');
                                        }
                                    })
                                    ->hintActions([
                                        Action::make('Copiar')
                                            ->label(false)
                                            ->icon('heroicon-o-document-duplicate')
                                            ->tooltip('Copiar valor')
                                            ->action(function (Placeholder $component, Set $set) {
                                                $set('valor', $component->getContent()->getAmount());
                                            }),
                                    ]),
                                Placeholder::make('ultimo_pago')
                                    ->key('ultimo_pago')
                                    ->live()
                                    ->content(function (Get $get) {
                                        if ($get('proyect_data.id')) {
                                            $related = Movement::where([
                                                ['id_proyecto', "=", $get('proyect_data.id')],
                                                ['tipo', "=", 'PAGO'],
                                            ])->latest('id')->first();
                                            return money($related->ingreso ?? 0, 'clp');
                                        } else {
                                            return money(0, 'clp');
                                        }
                                    })
                                    ->hintActions([
                                        Action::make('Copiar')
                                            ->label(false)
                                            ->icon('heroicon-o-document-duplicate')
                                            ->tooltip('Copiar valor')
                                            ->action(function (Placeholder $component, Set $set) {
                                                $set('valor', $component->getContent()->getAmount());
                                            }),
                                    ]),

                                MoneyInput::make('valor')
                                    ->required()
                                    ->formatStateUsing(function (?Model $record): string|null {
                                        return $record->cargo ?? $record->ingreso ?? null;
                                    }),
                            ]),
                        Forms\Components\Grid::make(3)
                            ->columnSpanFull()
                            ->schema([
                                Forms\Components\TextInput::make('cot')
                                    ->prefix('COT-'),
                                Forms\Components\TextInput::make('factura')
                                    ->maxLength(10)
                                    ->label('Nro. factura')
                                    ->prefix('N°')
                                    ->live()
                                    ->disabled(fn(Get $get) => $get('factura_pendiente')),
                                Forms\Components\Toggle::make('factura_pendiente')
                                    ->live()
                                    ->formatStateUsing(function (?Model $record): string|null {
                                        return isset($record->factura) ? ($record->factura == 'PEND' ? true : false) : false;
                                    })
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        // dd($set('factura', 0));
                                        $set('factura', null);
                                    })
                                    ->reactive()
                                    ->inline(false),
                            ]),
                        Forms\Components\RichEditor::make('detalle')
                            ->label('Descripción')
                            ->columnSpanFull()
                            ->disableAllToolbarButtons()
                            ->toolbarButtons(['bold', 'bulletList', 'italic', 'link', 'orderedList', 'redo', 'strike', 'undo'])
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Anexos')
                    ->columns(2)
                    // ->collapsed()
                    // ->description('Información extra')
                    ->icon('heroicon-o-paper-clip')
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
                            ->columnSpanFull()
                            ->disableAllToolbarButtons()
                            ->toolbarButtons(['bold', 'bulletList', 'italic', 'link', 'orderedList', 'redo', 'strike', 'undo'])
                            ->columnSpanFull(),

                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordClasses(fn(Model $record) => match ($record->tipo) {
                'VENTA' => 'venta',
                'PAGO' => 'pago',
            })
            ->paginationPageOptions([50, 100, 200, 'all'])
            ->defaultPaginationPageOption(50)
            ->recordUrl(
                fn(Model $record): string => MovementResource::getUrl('view', [$record->id]),
            )
            ->defaultSort('created_at', 'desc')
            ->groups([
                Group::make('id_proyecto')
                    ->label('Proyectos')
                    ->titlePrefixedWithLabel(false)
                    ->orderQueryUsing(fn(Builder $query) => $query->orderBy('id_proyecto', 'desc'))
                    ->getTitleFromRecordUsing(fn(Movement $record): string => ucfirst($record->proyect->customer->nombre . '/' . $record->proyect->titulo)),
                'factura',
            ])
            ->columns([
                Tables\Columns\ViewColumn::make('tipo')
                    ->label('Tipo')
                    ->view('tables.columns.movement-type-column')
                    ->placeholder('Sin registro.')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('fecha')
                    ->date()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('proyect.titulo_compuesto')
                    ->label('Proyecto')
                    // ->view('tables.columns.title-proyect-column')
                    ->placeholder('Sin registro.')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('cot')
                    ->label('Cotización')
                    ->placeholder('Sin registro.')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn(string $state) => !is_null($state) ?? 'COT-' . $state)
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('factura')
                    ->label('Factura')
                    ->placeholder('Sin registro.')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge()
                    // ->formatStateUsing(fn(?string $state) => !is_null($state) ?? 'COT-' . $state)
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('cargo')
                    ->numeric()
                    ->placeholder('$0')
                    ->currency('clp')
                    ->summarize(Sum::make()->money('clp', 1, 'es_CL')->label('Cargos'))
                    ->sortable()
                    ->searchable()
                    ->columnSpan(1),

                Tables\Columns\TextColumn::make('ingreso')
                    ->numeric()
                    ->placeholder('$0')
                    ->currency('clp')
                    ->summarize(Sum::make()->money('clp', 1, 'es_CL')->label('Ingresos'))
                    ->sortable()
                    ->searchable()
                    ->columnSpan(1),
            ])
            ->filters([
                DateRangeFilter::make('created_at')
                    ->placeholder('desde - hasta')
                    ->label('Filtrar por fecha'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\CreateAction::make()
                        ->label('Crear otro')
                        ->icon('heroicon-s-arrow-path')
                        ->stickyModalHeader()
                        ->stickyModalFooter()
                        // ->fillForm(fn(Movement $record): array => [
                        //     'proyect_data.id_cliente' => $record->proyect->customer->id,
                        // ])
                        ->using(function (array $data, string $model): Model {
                            $data['id_proyecto'] = $data['proyect_data']['id'];
                            $data['factura'] = $data['factura_pendiente'] ? 'PEND' : $data['factura'];
                            $data['cargo'] = ($data['tipo'] == 'VENTA') ? $data['valor'] : null;
                            $data['ingreso'] = ($data['tipo'] == 'PAGO') ? $data['valor'] : null;
                            $data['user_id'] = Auth()->id();
                            // dd($data);
                            return $model::create($data);
                        }),
                    Tables\Actions\EditAction::make()
                        ->stickyModalHeader()
                        ->stickyModalFooter()
                        ->using(function (Model $record, array $data): Model {
                            $data['id_proyecto'] = $data['proyect_data']['id'];
                            $data['factura'] = $data['factura_pendiente'] ? 'PEND' : $data['factura'];
                            $data['cargo'] = ($data['tipo'] == 'VENTA') ? $data['valor'] : null;
                            $data['ingreso'] = ($data['tipo'] == 'PAGO') ? $data['valor'] : null;
                            // dd($data);
                            $record->update($data);
                            return $record;
                        }),
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
            'index' => Pages\ManageMovements::route('/'),
            // 'create' => Pages\CreateMovement::route('/create'),
            'view' => Pages\ViewMovement::route('/{record}'),
            // 'edit' => Pages\EditMovement::route('/{record}/edit'),
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