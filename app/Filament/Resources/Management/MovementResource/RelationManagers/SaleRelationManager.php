<?php

namespace App\Filament\Resources\Management\MovementResource\RelationManagers;

use App\Filament\Resources\Management\SaleResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SaleRelationManager extends RelationManager
{
    protected static string $relationship = 'sale';

    protected static ?string $title = 'Ventas';

    protected static ?string $label = 'Venta';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return SaleResource::form($form);
    }
    public function table(Table $table): Table
    {
        return SaleResource::table($table)
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect(),
            ]);

        // return $table
        //     ->recordTitleAttribute('fecha_dcto')
        //     ->columns([
        //         Tables\Columns\TextColumn::make('fecha_dcto'),
        //     ])
        //     ->filters([
        //         //
        //     ])
        //     ->headerActions([
        //         Tables\Actions\CreateAction::make(),
        //         Tables\Actions\AttachAction::make(),
        //     ])
        //     ->actions([
        //         Tables\Actions\EditAction::make(),
        //         Tables\Actions\DetachAction::make(),
        //         Tables\Actions\DeleteAction::make(),
        //     ])
        //     ->bulkActions([
        //         Tables\Actions\BulkActionGroup::make([
        //             Tables\Actions\DetachBulkAction::make(),
        //             Tables\Actions\DeleteBulkAction::make(),
        //         ]),
        //     ]);
    }
}
