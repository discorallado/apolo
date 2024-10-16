<?php

namespace App\Filament\Resources\Management\CustomerResource\RelationManagers;

use App\Filament\Resources\Management\ProyectResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProyectsRelationManager extends RelationManager
{
    protected static string $relationship = 'proyects';

    public function form(Form $form): Form
    {
        return ProyectResource::form($form);
        //     return $form
        //         ->schema([
        //             Forms\Components\TextInput::make('titulo')
        //                 ->required()
        //                 ->maxLength(255),
        //         ]);
    }

    public function table(Table $table): Table
    {
        return ProyectResource::table($table);

        // return $table
        //     ->recordTitleAttribute('titulo')
        //     ->columns([
        //         Tables\Columns\TextColumn::make('titulo'),
        //     ])
        //     ->filters([
        //         //
        //     ])
        //     ->headerActions([
        //         Tables\Actions\CreateAction::make(),
        //     ])
        //     ->actions([
        //         Tables\Actions\EditAction::make(),
        //         Tables\Actions\DeleteAction::make(),
        //     ])
        //     ->bulkActions([
        //         Tables\Actions\BulkActionGroup::make([
        //             Tables\Actions\DeleteBulkAction::make(),
        //         ]),
        //     ]);
    }
}