<?php

namespace App\Filament\Resources\Management\PurchaseResource\Pages;

use App\Filament\Resources\Management\PurchaseResource;
use App\Filament\Resources\Management\PurchaseResource\Widgets\PurchaseStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePurchases extends ManageRecords
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = auth()->id();
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

    public function getHeaderWidgetsColumns(): int | array
    {
        return 4;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PurchaseStatsWidget::class,
        ];
    }
}