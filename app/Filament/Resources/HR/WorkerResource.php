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
                Forms\Components\TextInput::make('valor_dia')
                    ->prefixIcon('heroicon-o-currency-dollar')
                    ->required()
                    ->numeric()
                    ->maxLength(11),
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
                Tables\Columns\TextColumn::make('valor_dia')
                    ->money('CLP', 0, 'cl')
                    ->searchable(),
                Tables\Columns\TextColumn::make('detalles')
                    ->placeholder('Sin detalles.')
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
                Tables\Actions\EditAction::make()
                    ->button()

                    ->size(ActionSize::ExtraSmall)
                    ->button()

                    ->size(ActionSize::ExtraSmall),
                Tables\Actions\DeleteAction::make()
                    ->button()

                    ->size(ActionSize::ExtraSmall),
                Tables\Actions\ForceDeleteAction::make()
                    ->button()

                    ->size(ActionSize::ExtraSmall),
                Tables\Actions\RestoreAction::make()
                    ->button()

                    ->size(ActionSize::ExtraSmall),
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
