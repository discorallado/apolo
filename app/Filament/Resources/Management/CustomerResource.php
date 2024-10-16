<?php

namespace App\Filament\Resources\Management;

use App\Filament\Resources\Management\CustomerResource\Pages;
use App\Filament\Resources\Management\CustomerResource\RelationManagers\ProyectsRelationManager;
use App\Models\Management\Customer;
use App\Models\Management\Proyect;
use App\Settings\GeneralSettings;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'man/clientes';

    protected static ?string $modelLabel = 'cliente';

    protected static ?string $pluralModelLabel = 'Clientes';

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static ?string $navigationGroup = 'Gestión';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(1)
                    ->schema([
                        Forms\Components\Section::make('Detalles')
                            ->icon('heroicon-o-document-magnifying-glass')
                            ->description('Indique datos de identificación')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('nombre')
                                    ->columnSpan(1)
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('rut')
                                    ->mask('99.999.999-*')
                                    ->placeholder('12.345.678-9')
                                    ->columnSpan(1)
                                    ->maxLength(21),
                                Forms\Components\TextInput::make('giro')
                                    ->columnSpan(2)
                                    ->maxLength(65535)
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Section::make('Contacto')
                            ->icon('heroicon-o-user-circle')
                            ->description('Indique datos de contacto')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('direccion')
                                    ->columnSpan(2)
                                    ->maxLength(65535)
                                    ->columnSpanFull(),
                                Forms\Components\Select::make('ciudad')
                                    ->options(collect(app(GeneralSettings::class)->comunas)->all())
                                    ->searchable()
                                    ->live()
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('telefono')
                                    ->columnSpan(1)
                                    ->prefix('+56')
                                    ->tel()
                                    ->maxLength(50),

                            ])
                    ])

                    ->columnSpan(['lg' => fn(?Customer $record) => $record === null ? 3 : 2]),

                Forms\Components\Section::make()
                    ->description('Información de los datos')
                    ->icon('heroicon-s-information-circle')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(fn(Customer $record): ?string => $record->created_at?->diffForHumans()),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last modified at')
                            ->content(fn(Customer $record): string => ($record->updated_at ? $record->updated_at->diffForHumans() : 'Sin modificaciones')),

                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn(?Customer $record) => $record === null),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginationPageOptions([25, 50, 100, 200, 'all'])
            ->defaultPaginationPageOption(50)
            ->defaultSort('nombre', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('rut')
                    ->formatStateUsing(function (string $state) {
                        $state = explode('-', $state);
                        $state[0] = number_format($state[0], 0, '', '.');
                        return implode('-', $state);
                    })
                    ->copyable()
                    ->placeholder('Sin rut.')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->limit(40)
                    ->copyable()
                    ->searchable()
                    ->numeric()
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    })
                    ->sortable(),
                Tables\Columns\ViewColumn::make('ciudad')
                    ->view('tables.columns.city-column')
                    ->placeholder('Sin registro.')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('telefono')
                    ->placeholder('Sin registro.')
                    ->copyable()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modificado')
                    ->placeholder('Nunca.')
                    ->dateTime()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Eliminado')
                    ->placeholder('Nunca.')
                    ->dateTime()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                DateRangeFilter::make('created_at')
                    ->placeholder('desde - hasta')
                    ->label('Filtrar por fecha'),
                Tables\Filters\TrashedFilter::make()
                    ->label('Mostrar registros eliminados'),
            ])
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label('Filtrar'),
            )
            ->actions([
                // Tables\Actions\ActionGroup::make([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                // ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ProyectsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
