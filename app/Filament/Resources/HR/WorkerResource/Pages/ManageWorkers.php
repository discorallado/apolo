<?php

namespace App\Filament\Resources\HR\WorkerResource\Pages;

use App\Filament\Resources\HR\WorkerResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\ActionSize;
use Illuminate\Database\Eloquent\Model;

class ManageWorkers extends ManageRecords
{
    protected static string $resource = WorkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->button()
                //
                ->using(function (array $data, string $model): Model {
                    $data['user_id'] = auth()->id();
                    // dd($data);
                    return $model::create($data);
                }),
        ];
    }
}
