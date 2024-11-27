<?php

namespace App\Filament\Resources\ListResource\Pages;

use App\Filament\Resources\ListResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateList extends CreateRecord
{
    protected static string $resource = ListResource::class;
}
