<?php

namespace App\Filament\Imports\Management;

use App\Models\Management\Purchase;
use Carbon\Carbon;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class PurchaseImporter extends Importer
{
    protected static ?string $model = Purchase::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('folio')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->guess(['Folio']),

            ImportColumn::make('fecha_dcto')
                ->guess(['Fecha Docto'])
                ->rules(['date']),

            ImportColumn::make('tipo_doc')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('exento')
                ->ignoreBlankState()
                ->guess(['Monto Exento'])
                ->rules(['integer']),

            ImportColumn::make('neto')
                ->guess(['Monto Neto'])
                ->rules(['required', 'integer']),

            ImportColumn::make('iva')
                ->guess(['Monto IVA'])
                ->rules(['integer']),

            ImportColumn::make('total')
                ->guess(['Monto Total'])
                ->rules(['required', 'integer']),

            ImportColumn::make('user_id'),
        ];
    }

    public function resolveRecord(): ?Purchase
    {
        // return Sale::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);
        $this->data['fecha_dcto'] = Carbon::parse($this->data['fecha_dcto'])->format('Y-m-d');
        $this->data['user_id'] = "1";
        // dd($this->data);
        return new Purchase();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your purchase import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
