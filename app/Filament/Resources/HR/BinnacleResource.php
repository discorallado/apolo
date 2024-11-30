<?php

namespace App\Filament\Resources\HR;

use App\Filament\Resources\HR\BinnacleResource\Pages;
use App\Filament\Resources\HR\BinnacleResource\RelationManagers;
use App\Models\HR\Binnacle;
use App\Models\HR\Payment;
use App\Models\HR\Worker;
use App\Models\Management\Proyect;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\ActionSize;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;

class BinnacleResource extends Resource
{
    protected static ?string $model = Binnacle::class;

    protected static ?string $slug = 'bitacora';

    protected static ?string $modelLabel = 'Bitácora';

    protected static ?string $pluralModelLabel = 'Bitácoras';

    protected static ?string $recordTitleAttribute = 'worker.nombre';

    protected static ?string $navigationGroup = 'RRHH';

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\Select::make('id_trabajador')
                    ->options(Worker::query()->pluck('nombre', 'id'))
                    ->live()
                    ->searchable()
                    ->label('Trabajador')
                    ->required(),
                Forms\Components\Select::make('id_proyecto')
                    ->options(Proyect::query()->pluck('titulo', 'id'))
                    ->live()
                    ->searchable()
                    ->label('Proyecto')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('valor_dia'),
                Forms\Components\MarkdownEditor::make('detalles')
                    ->columnSpanFull(),
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Inicio'),
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Fin'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('worker.nombre')
                    ->label('Trabajador')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Días trabajados')
                    ->placeholder('Sin registro.')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('starts_at')

                //     ->label('Inicio')
                //     ->date()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('ends_at')
                //     ->label('Fin')
                //     ->date()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('dias')
                    ->label('Cantidad')
                    ->placeholder('Sin registro.')
                    ->suffix(' día/s')
                    ->sortable(),
                MoneyColumn::make('valor_dia')
                    ->label('Valor')
                    ->placeholder('Sin registro.')
                    // ->money('CLP', 0, 'cl')
                    ->sortable(),
                MoneyColumn::make('total_dias')
                    ->label('Total')
                    // ->money('CLP', 0, 'cl')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('payments_sum_monto')
                //     ->sum('payments', 'monto')
                //     ->label('Pagado $')
                //     ->money('CLP', 0, 'cl')
                //     ->placeholder('Sin pago'),

                Tables\Columns\TextColumn::make('proyect.titulo')
                    ->label('Proyecto')
                    ->placeholder('Sin asignar')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Actions\ActionGroup::make([
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageBinnacles::route('/'),
            'calendar' => Pages\Calendar::route('/calendar'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getWidgets(): array
    {
        return [
            BinnacleResource\Widgets\CalendarWidget2::class,
        ];
    }
}
