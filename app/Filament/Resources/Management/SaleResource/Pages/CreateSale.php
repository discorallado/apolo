<?php

namespace App\Filament\Resources\Management\SaleResource\Pages;

use App\Filament\Resources\Management\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSale extends CreateRecord
{
	protected static string $resource = SaleResource::class;


	protected function mutateFormDataBeforeCreate(array $data): array
	{
		dd($data);
		$data['user_id'] = auth()->id();
		return $data;
	}
}
