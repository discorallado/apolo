<?php

namespace App\Filament\Resources\Management\CustomerResource\RelationManagers;

use App\Filament\Resources\Management\CustomerResource;
use App\Filament\Resources\Management\ProyectResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProyectsRelationManager extends RelationManager
{
    protected static ?string $title = 'Proyectos';

    protected static ?string $label = 'Proyecto';

    protected static string $relationship = 'proyects';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return ProyectResource::form($form);
    }

    public function table(Table $table): Table
    {
        return ProyectResource::table($table)
            ->recordUrl(
                fn(Model $record): string => ProyectResource::getUrl('view', [$record->id]),
            );
    }
}