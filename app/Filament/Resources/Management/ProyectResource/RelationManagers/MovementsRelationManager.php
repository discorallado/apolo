<?php

namespace App\Filament\Resources\Management\ProyectResource\RelationManagers;

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
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MovementsRelationManager extends RelationManager
{
    protected static ?string $title = 'Ventas/Pagos';

    protected static ?string $label = 'Venta/Pago';

    protected static string $relationship = 'movements';

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
                                    ->affix('COT-')
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
            ->recordTitleAttribute('created_at')
            ->inverseRelationship('proyect')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->paginated(false)
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
                Tables\Columns\TextColumn::make('cot')
                    ->label('Cotizacion')
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
                    ->sortable()
                    ->searchable()
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
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // dd($data);
                        $data['user_id'] = auth()->id();

                        if ($data['tipo_factura'] != 'numero') {
                            $data['factura'] = $data['tipo_factura'];
                        }

                        $data['id_proyecto'] = $data['proyect_data']['id'];
                        unset($data['proyect_data']);
                        unset($data['tipo_factura']);
                        unset($data['cliente']);
                        return $data;
                    }),
                Tables\Actions\AssociateAction::make()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DissociateAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
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