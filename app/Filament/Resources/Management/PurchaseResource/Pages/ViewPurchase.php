<?php

namespace App\Filament\Resources\Management\PurchaseResource\Pages;

use App\Filament\Resources\Management\PurchaseResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewPurchase extends ViewRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['total'] = (int)$data['total'];
                    if (filled($data['proyect_data']['id'])) {
                        $data['id_cliente'] = null;
                        $data['id_proyecto'] = $data['proyect_data']['id'];
                    } else {
                        $data['id_cliente'] = $data['proyect_data']['id_cliente'];
                        $data['id_proyecto'] = null;
                    }
                    return $data;
                }),
        ];
    }
}