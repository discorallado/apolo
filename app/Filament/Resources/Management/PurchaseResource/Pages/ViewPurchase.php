<?php

namespace App\Filament\Resources\Management\PurchaseResource\Pages;

use App\Filament\Resources\Management\PurchaseResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewPurchase extends Infolist
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
