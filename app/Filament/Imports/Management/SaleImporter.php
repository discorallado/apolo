<?php

namespace App\Filament\Imports\Management;

use App\Models\Management\Sale;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class SaleImporter extends Importer
{
    protected static ?string $model = Sale::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('Folio')
                ->label('Folio')
                ->requiredMapping()
                // ->rules(['required', 'max:255'])
                ->guess(['folio']),
            ImportColumn::make('Fecha Docto')
                // ->rules(['date'])
                ->guess(['fecha_dcto']),
            ImportColumn::make('Tipo Doc')
                ->requiredMapping()
                // ->rules(['required', 'max:255'])
                ->guess(['tipo_doc']),
            ImportColumn::make('Monto Exento')
                ->numeric()
                // ->rules(['integer'])
                ->guess(['excento']),
            ImportColumn::make('Monto Neto')
                ->requiredMapping()
                // ->numeric()
                // ->rules(['required', 'integer'])
                ->guess(['neto']),
            ImportColumn::make('Monto IVA')
                ->numeric()
                // ->rules(['integer'])
                ->guess(['iva']),
            ImportColumn::make('Monto total')
                ->requiredMapping()
                ->numeric()
                // ->rules(['required', 'integer'])
                ->guess(['total']),
        ];
    }

    public function resolveRecord(): ?Sale
    {
        // return Sale::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Sale();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your sale import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
