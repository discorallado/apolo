<?php

namespace App\Filament\Resources\Management;

use App\Filament\Resources\Management\SupplierResource\Pages;
use App\Filament\Resources\Management\SupplierResource\RelationManagers;
use App\Models\Management\Supplier;
use App\Settings\GeneralSettings;
use Closure;
use Dotenv\Util\Str;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Freshwork\ChileanBundle\Rut;
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
                                    ->placeholder('sin registro.')
                                    ->formatStateUsing(fn(string $state): string => strtoupper($state))
                                    // ->columnSpanFull()
                                    ->columnSpan(3),
                            ]),
                        Section::make('Contacto')
                            ->icon('heroicon-o-user-circle')
                            ->description('Indique datos de contacto')
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
                                    ->columnSpan(1),
                                TextEntry::make('telefono')
                                    ->placeholder('sin registro.')
                                    ->columnSpan(1)
                                    ->prefix('+56'),
                            ]),
                        Section::make('Archivos')
                            ->icon('heroicon-s-paper-clip')
                            ->description('Documentos adjuntos.')
                            ->schema([
                                ViewEntry::make('supplier_files')
                                    ->label(false)
                                    ->view('infolists.components.files-entry')
                                    ->state(fn(Model $record) => $record->getMedia('proveedores'))
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
                                    ->rules([
                                        fn(): Closure => function (string $attribute, $value, Closure $fail) {
                                            if (!Rut::parse($value)->quiet()->validate()) {
                                                $fail('The :attribute is invalid.');
                                            }
                                        },
                                    ])
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
                                SpatieMediaLibraryFileUpload::make('supplier_files')
                                    ->label(false)
                                    ->collection('proveedores')
                                    ->multiple()
                                    ->openable()
                                    ->downloadable()
                                    ->deletable()
                                    ->previewable()
                                    ->columnSpanFull(),
                            ]),
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
                    ->formatStateUsing(fn(string $state) => Rut::parse($state))

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
                // Tables\Columns\ViewColumn::make('id_ciudad')
                //     ->view('tables.columns.city-column')
                //     ->placeholder('Sin registro.')
                //     ->sortable()
                //     ->searchable(),

                Tables\Columns\TextColumn::make('ciudad')
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