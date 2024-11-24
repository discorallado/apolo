<?php

namespace App\Filament\Resources\HR;

use App\Filament\Resources\HR\PaymentResource\Pages;
use App\Filament\Resources\HR\PaymentResource\RelationManagers;
use App\Models\HR\Binnacle;
use App\Models\HR\Payment;
use App\Models\HR\Worker;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $slug = 'hr/pagos';

    protected static ?string $modelLabel = 'Pago';

    protected static ?string $pluralModelLabel = 'Pagos';

    protected static ?string $recordTitleAttribute = 'binnacle.title';

    protected static ?string $navigationGroup = 'RRHH';

    // protected static ?string $navigationParentItem = 'Bitácoras';


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id_bitacora')
                    ->options(Binnacle::all()->pluck('title', 'id'))
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $set('id_trabajador', 1);
                    }),
                Forms\Components\Select::make('id_trabajador')
                    ->options(Worker::all()->pluck('nombre', 'id'))
                    ->live(),
                // ->disabled()
                // ->required(),
                Forms\Components\DateTimePicker::make('fecha')
                    ->default(now())
                    ->required(),
                Forms\Components\TextInput::make('monto')
                    ->required()
                    ->numeric(),
                FileUpload::make('attachments')
                    ->directory('adjuntos_pagos')
                    ->multiple()
                    ->storeFileNamesIn('attachment_file_names')
                    ->openable()
                    ->downloadable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Pagos realizados')
            ->defaultSort('fecha', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('fecha')
                    ->label('Fecha pago')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('binnacle.title')
                    ->label('Días pagados')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('id')
                //     ->formatStateUsing(fn(string $state): string => Payment::where('id_bitacora', '=', $state)->count()),
                // Tables\Columns\TextColumn::make('ends_at')
                //     ->dateTime()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('binnacle.worker.nombre')
                    ->label('Trabajador')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('monto')
                    ->label('Pago')
                    ->numeric()
                    ->money('clp', 0, 'cl')
                    ->sortable(),
                Tables\Columns\TextColumn::make('attachments')
                    ->label('Adjunto')
                    ->placeholder('S/A')
                    ->formatStateUsing(fn(string $state): string => count(explode(', ', $state)))
                    ->color('success')
                    ->iconPosition('after')
                    ->icon('heroicon-o-paper-clip'),
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
                // Tables\Actions\ActionGroup::make([
                // Tables\Actions\ViewAction::make()->label('Ver')->button()->size(ActionSize::ExtraSmall),
                Tables\Actions\EditAction::make()->button()->size(ActionSize::ExtraSmall),
                Tables\Actions\DeleteAction::make()->button()->size(ActionSize::ExtraSmall),
                // ])->dropdown(false),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePayments::route('/'),
            // 'index' => Pages\ListPayments::route('/'),
            // 'create' => Pages\CreatePayment::route('/create'),
            // 'view' => Pages\ViewPayment::route('/{record}'),
            // 'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // return BinnacleResource::getEloquentQuery()
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getWidgets(): array
    {
        return [
            PaymentResource\Widgets\TablaWidget::class,
        ];
    }
}
