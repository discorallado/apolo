<?php

namespace App\Filament\Resources\HR;

use App\Filament\Resources\HR\WorkerResource\Pages;
use App\Filament\Resources\HR\WorkerResource\RelationManagers;
use App\Models\HR\Worker;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\ActionSize;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;

class WorkerResource extends Resource
{
    protected static ?string $model = Worker::class;

    protected static ?string $slug = 'trabajadores';

    protected static ?string $modelLabel = 'Trabajador';

    protected static ?string $pluralModelLabel = 'Trabajadores';

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static ?string $navigationGroup = 'RRHH';

    // protected static ?string $navigationParentItem = 'Bitácoras';

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),
                MoneyInput::make('valor_dia')
                    // ->prefixIcon('heroicon-o-currency-dollar')
                    ->required()
                    // ->numeric()
                    ->maxLength(11),
                Forms\Components\ColorPicker::make('color'),
                Forms\Components\RichEditor::make('detalles')
                    ->maxLength(255)
                    ->default(null),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('initials')
                    ->label('Iniciales')
                    ->searchable(),
                Tables\Columns\ColorColumn::make('color'),
                Tables\Columns\TextColumn::make('detalles')
                    ->placeholder('Sin detalles.')
                    ->limit(30)
                    ->searchable(),
                MoneyColumn::make('valor_dia')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ])->dropdown(false),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWorkers::route('/'),
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
