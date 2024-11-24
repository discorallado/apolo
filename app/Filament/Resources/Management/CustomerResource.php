<?php

namespace App\Filament\Resources\Management;

use App\Filament\Resources\Management\CustomerResource\Pages;
use App\Filament\Resources\Management\CustomerResource\RelationManagers\ProyectsRelationManager;
use App\Models\Management\Customer;
use App\Models\Management\Proyect;
use App\Settings\GeneralSettings;
use Closure;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Freshwork\ChileanBundle\Rut;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'clientes';

    protected static ?string $modelLabel = 'cliente';

    protected static ?string $pluralModelLabel = 'Clientes';

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static ?string $navigationGroup = 'Gestión';

    protected static ?string $navigationIcon = 'heroicon-o-users';

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
                            ->description('Datos de identificación')
                            ->columns(3)
                            ->columnSpan(2)
                            ->schema([
                                TextEntry::make('rut')
                                    ->placeholder('sin rut.')
                                    ->formatStateUsing(fn(string $state) => Rut::parse($state)->quiet()->validate() ? Rut::parse($state) : $state . ' (Rut inválido)')
                                    ->columnSpan(1),
                                TextEntry::make('nombre')
                                    ->formatStateUsing(fn(string $state): string => strtoupper($state))
                                    ->columnSpan(2),
                                TextEntry::make('giro')
                                    ->formatStateUsing(fn(string $state): string => strtoupper($state))
                                    ->placeholder('sin registro.')
                                    ->columnSpan(3),
                            ]),
                        Section::make('Contacto')
                            ->icon('heroicon-o-user-circle')
                            ->description('Datos de contacto')
                            ->columns(3)
                            ->columnSpan(2)
                            ->schema([
                                TextEntry::make('direccion')
                                    ->formatStateUsing(fn(string $state): string => strtoupper($state))
                                    ->placeholder('sin registro.')
                                    ->columnSpan(2),
                                TextEntry::make('city_name')
                                    ->placeholder('sin registro.')
                                    // ->formatStateUsing(fn($state, GeneralSettings $generalSettings) => strtoupper($generalSettings->comunas[(int)$state]))
                                    ->columnSpan(1),
                                TextEntry::make('email')
                                    ->placeholder('sin registro.')
                                    ->columnSpan(2),
                                TextEntry::make('telefono')
                                    ->placeholder('sin registro.')
                                    ->columnSpan(1)
                                    ->prefix('+56'),
                            ]),
                        Section::make('Archivos')
                            ->icon('heroicon-s-paper-clip')
                            ->description('Documentos adjuntos.')
                            ->schema([
                                ViewEntry::make('customer_files')
                                    ->label(false)
                                    ->view('infolists.components.files-entry')
                                    ->state(fn(Model $record) => $record->getMedia('clientes'))
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
            ->schema([
                // Forms\Components\Grid::make(1)
                //     ->schema([
                Forms\Components\Section::make('Detalles')
                    ->icon('heroicon-o-document-magnifying-glass')
                    // ->description('Indique datos de identificación')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->columnSpan(1)
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('rut')
                            ->mask('99.999.999-*')
                            ->placeholder('12.345.678-K')
                            ->rules([
                                fn(): Closure => function (string $attribute, $value, Closure $fail) {
                                    if (!Rut::parse($value)->quiet()->validate()) {
                                        $fail('The :attribute is invalid.');
                                    }
                                },
                            ])
                            ->columnSpan(1)
                            ->maxLength(21),
                        Forms\Components\TextInput::make('giro')
                            ->columnSpan(2)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Contacto')
                    ->icon('heroicon-o-user-circle')
                    // ->description('Indique datos de contacto')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('direccion')
                            ->columnSpan(2)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('id_ciudad')
                            ->options(collect(app(GeneralSettings::class)->comunas)->all())
                            ->searchable()
                            ->live()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('telefono')
                            ->columnSpan(1)
                            ->prefix('+56')
                            ->tel()
                            ->maxLength(50),

                    ]),
                Forms\Components\Section::make('Archivos')
                    ->icon('heroicon-o-paper-clip')
                    ->collapsed()
                    // ->description('Archivos adjuntos.')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('customer_files')
                            ->label(false)
                            ->collection('clientes')
                            ->multiple()
                            ->openable()
                            ->downloadable()
                            ->deletable()
                            ->previewable()
                            ->columnSpanFull(),
                    ]),
                // ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginationPageOptions([25, 50, 100, 200, 'all'])
            ->defaultPaginationPageOption(50)
            // ->defaultSort('nombre', 'desc')
            ->recordUrl(
                fn(Model $record): string => CustomerResource::getUrl('view', [$record->id]),
            )
            ->columns([
                Tables\Columns\TextColumn::make('rut')
                    ->formatStateUsing(fn(string $state) => Rut::parse($state))
                    // ->copyable()
                    ->placeholder('Sin rut.')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->limit(40)
                    // ->copyable()
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
                // Tables\Columns\ViewColumn::make('ciudad')
                //     // Tables\Columns\TextColumn::make('ciudad')
                //     ->view('tables.columns.city-column')
                //     ->placeholder('Sin registro.')
                //     ->sortable()
                //     ->searchable(),

                Tables\Columns\TextColumn::make('ciudad')
                    ->placeholder('Sin registro.')
                    // ->copyable()
                    // ->icon('heroicon-o-phone')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('telefono')
                    ->placeholder('Sin registro.')
                    // ->copyable()
                    ->icon('heroicon-o-phone')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('proyects_count')
                    ->label('Proyectos')
                    ->sortable()
                    ->counts('proyects')
                    ->formatStateUsing(fn($state): string|HtmlString => $state > 0 ? $state . ($state == 1 ? ' proy.' : ' proys.') : new HtmlString('<span class="text-gray-400 dark:text-gray-500">Sin proy.</<span>')),

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
                // Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ManageCustomers::route('/'),
            // 'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            // 'edit' => Pages\EditCustomer::route('/{record}/edit'),
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