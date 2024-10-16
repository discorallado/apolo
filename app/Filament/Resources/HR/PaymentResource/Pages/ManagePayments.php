<?php

namespace App\Filament\Resources\HR\PaymentResource\Pages;

use App\Filament\Resources\HR\BinnacleResource;
use App\Filament\Resources\HR\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePayments extends ManageRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        return [
            PaymentResource\Widgets\TablaWidget::class,
        ];
    }
}