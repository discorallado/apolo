<?php

namespace App\Filament\Resources\Management;

use App\Filament\Resources\Management\SupplierResource\Pages;
use App\Filament\Resources\Management\SupplierResource\RelationManagers;
use App\Models\Management\Supplier;
use App\Settings\GeneralSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'proveedores';

    protected static ?string $modelLabel = 'Proveedor';

    protected static ?string $pluralModelLabel = 'Proveedores';

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static ?string $navigationGroup = 'Gestión';

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(3)
            ->schema([
                Grid::make(1)
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Detalles')
                            ->icon('heroicon-o-document-magnifying-glass')
                            ->description('Indique datos de identificación')
                            ->schema([
                                TextEntry::make('nombre')
                                    ->placeholder('sin registro.')
                                    ->columnSpan(1),
                                TextEntry::make('rut')
                                    ->placeholder('sin registro.')
                                    ->state(function (string $state) {
                                        // $state = explode('-', $state);
                                        // $state[0] = number_format($state[0], 0, '', '.');
                                        // return implode('-', $state);
                                        return $state;
                                    })
                                    ->columnSpan(1),
                                TextEntry::make('giro')
                                    ->placeholder('sin registro.')
                                    ->columnSpan(2),
                            ])
                            ->columns(2)
                            ->columnSpan(2),
                        Section::make('Contacto')
                            ->icon('heroicon-o-user-circle')
                            ->description('Indique datos de contacto')
                            ->columns(2)
                            ->columnSpan(2)
                            ->schema([
                                TextEntry::make('direccion')
                                    ->placeholder('sin registro.')
                                    ->columnSpan(2),
                                TextEntry::make('ciudad')
                                    ->placeholder('sin registro.')
                                    // ->options(collect(app(GeneralSettings::class)->comunas)->all())
                                    ->columnSpan(1),
                                TextEntry::make('telefono')
                                    ->placeholder('sin registro.')
                                    ->columnSpan(1)
                                    ->prefix('+56'),
                            ]),
                    ]),
                Grid::make(1)
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Contacto')
                            ->icon('heroicon-s-information-circle')
                            ->description('Información de los datos')
                            ->columns(1)
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created at')
                                    ->state(fn(Supplier $record): ?string => $record->created_at?->diffForHumans()),

                                TextEntry::make('updated_at')
                                    ->label('Last modified at')
                                    ->state(fn(Supplier $record): string => ($record->updated_at ? $record->updated_at->diffForHumans() : 'Sin modificaciones')),
                            ])
                    ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(1)
                    ->schema([

                        Forms\Components\Section::make('Detalles')
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
                                    ->required(),
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
                    ]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginationPageOptions([25, 50, 100, 200, 'all'])
            ->defaultPaginationPageOption(50)
            // ->defaultSort('nombre', 'asc')
            ->recordUrl(
                fn(Model $record): string => SupplierResource::getUrl('view', [$record->id]),
            )
            ->columns([
                Tables\Columns\TextColumn::make('rut')
                    ->formatStateUsing(function (string $state) {
                        $state = explode('-', $state);
                        $state[0] = number_format($state[0], 0, '', '.');
                        return implode('-', $state);
                    })
                    ->placeholder('Sin registro.')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->limit(40)
                    ->searchable()
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
                    ->sortable()
                    ->placeholder('Sin registro.')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->searchable()
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->searchable()
                    ->placeholder('Nunca.')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->placeholder('Nunca.')
                    ->dateTime()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                // Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                // ]),
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
            // 'index' => Pages\ListSuppliers::route('/'),
            'index' => Pages\ManageSuppliers::route('/'),
            // 'create' => Pages\CreateSupplier::route('/create'),
            'view' => Pages\ViewSupplier::route('/{record}'),
            // 'edit' => Pages\EditSupplier::route('/{record}/edit'),
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
