<?php

namespace App\Filament\Resources\HR\BinnacleResource\Pages;

use App\Filament\Resources\HR\BinnacleResource;
use App\Filament\Resources\HR\BinnacleResource\Widgets\CalendarWidget;
use App\Filament\Resources\HR\BinnacleResource\Widgets\CalendarWidget2;
use Filament\Resources\Pages\Page;

class Calendar extends Page
{
    protected static string $resource = BinnacleResource::class;

    protected static ?string $title = 'Bitacora de trabajos';

    protected static string $view = 'filament.resources.h-r.binnacle-resource.pages.calendar';

    protected function getHeaderWidgets(): array
    {
        return [
            CalendarWidget2::class
        ];
    }
}
