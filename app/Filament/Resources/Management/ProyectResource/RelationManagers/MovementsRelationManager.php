<?php

namespace App\Filament\Resources\Management\ProyectResource\RelationManagers;

use App\Filament\Resources\Management\MovementResource;
use App\Filament\Resources\Management\ProyectResource;
use App\Forms\Components\CustomerProyectField;
use App\Models\Management\Proyect;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class MovementsRelationManager extends RelationManager
{
    protected static ?string $title = 'Movimientos';

    protected static ?string $label = 'Venta/Pago';

    protected static string $relationship = 'movements';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Grid::make(2)
                    ->schema([
                        CustomerProyectField::make('proyect_data')
                            ->relationship('proyect')
                            ->label(false)
                            ->columnSpanFull()
                            ->afterStateHydrated(function (CustomerProyectField $component) {
                                return $component->state([
                                    'id' => $this->getOwnerRecord()->id,
                                    'id_cliente' => $this->getOwnerRecord()->id_cliente,
                                ]);
                            }),

                        Forms\Components\Section::make('Detalles')
                            ->columns(3)
                            ->schema([
                                Forms\Components\DatePicker::make('fecha')
                                    ->default(\now())
                                    ->required(),

                                Forms\Components\Placeholder::make('monto_proyecto')
                                    ->hintIcon('heroicon-s-information-circle')
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
                                                        ->prefix('$')
                                                        ->numeric()
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
                                                        ->prefix('$')
                                                        ->numeric()
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
                                    // ->affix('COT-')
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

                    ])
            ]);
    }

    public function table(Table $table): Table
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
            ->defaultSort('fecha', 'asc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->createAnother()
                    ->stickyModalHeader()
                    ->stickyModalFooter()
                    ->using(function (array $data, string $model): Model {
                        // dd($data);
                        $data['id_proyecto'] = $data['proyect_data']['id'];
                        $data['factura'] = $data['factura_pendiente'] ? 'PEND' : $data['factura'];
                        $data['cargo'] = ($data['tipo'] == 'VENTA') ? $data['valor'] : null;
                        $data['ingreso'] = ($data['tipo'] == 'PAGO') ? $data['valor'] : null;
                        $data['user_id'] = Auth()->id();
                        return $model::create($data);
                    }),
            ])
            ->columns([
                Tables\Columns\ViewColumn::make('tipo')
                    ->label('tipo')
                    ->view('tables.columns.movement-type-column')
                    ->placeholder('Sin registro.')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('fecha')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('cot')
                    ->label('Cotizacion')
                    ->placeholder('sin registro.')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('factura')
                    ->placeholder('sin registro.')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('cargo')
                    ->numeric()
                    ->placeholder('$0')
                    ->currency('clp')
                    ->summarize(Sum::make()->money('clp', 1, 'es_CL')->label('Cargos'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('ingreso')
                    ->numeric()
                    ->placeholder('$0')
                    ->currency('clp')
                    ->summarize(Sum::make()->money('clp', 1, 'es_CL')->label('Ingresos'))
                    ->sortable()
                    ->searchable(),
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
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //         Tables\Actions\ForceDeleteBulkAction::make(),
            //         Tables\Actions\RestoreBulkAction::make(),
            //     ])->dropdown(false),
            // ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}