<?php

namespace App\Filament\Reports;

use App\Models\Management\Movement;
use App\Models\Management\Proyect;
use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;


class DeudaTotalReport extends Report
{
    public ?string $heading = "Sales Report";
    public ?string $subHeading = "Periodic sales report";

    public ?string $reportTitle = "Sales Report";
    public ?string $reportSubTitle = "General summarised sales report for the selected period";

    // public ?string $subHeading = "A great report";

    public function header(Header $header): Header
    {
        return $header
            ->schema([
                // ...

            ]);
    }

    public function body(Body $body): Body
    {
        return $body
            ->schema([
                Body\Layout\BodyColumn::make()
                    ->schema([
                        Body\Table::make()
                            ->columns([
                                Body\TextColumn::make("id"),
                                    // ->label("Date"),
                                // Body\TextColumn::make("monto_proyectado")
                                //     // ->money("USD")
                                //     ->label("Total Sales")
                                //     ->alignRight(),
                                //     // ->sum(),
                            ])
                            ->data(
                                fn(?array $filters) => $this->getData($filters)
                            ),
                    ]),
            ]);
    }

    public function footer(Footer $footer): Footer
    {
        return $footer
            ->schema([
                // ...
            ]);
    }

    public function filterForm(Form $form): Form
    {
        return $form
            ->schema([
                // Select::make('status')
                // ->placeholder('Status')
                // ->options([
                //     'active' => 'Active',
                //     'inactive' => 'Inactive',
                // ]),
                DateRangePicker::make("created_at")
                    ->label("Rango de fecha")
                    // ->placeholder("Select a date range")
            ]);
    }

    private function getData(?array $filters): Collection
    {
        [$from, $to, $range] = getCarbonInstancesFromDateString($filters["created_at"] ?? null);
        $trend = Trend::model(Movement::class)
        // $trend = Trend::query(
        //         Proyect::query()
        //         ->with('movements')
        //     )
            ->dateColumn("created_at")
            ->between($from ?? now()->subYear(10), $to ?? now())
            ->{$range}()
            ->count();

        return $trend->map(fn(TrendValue $value) => [
            "date" => match ($range) {
                "perMonth" => Carbon::parse($value->date)->format("M, Y"),
                "perYear" => Carbon::parse($value->date)->format("Y"),
                default => Carbon::parse($value->date)->format("d/m/Y"),
            },
            "value" => (float)$value->aggregate
        ]);
    }
}
