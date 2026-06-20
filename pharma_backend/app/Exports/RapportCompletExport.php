<?php
// app/Exports/RapportCompletExport.php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RapportCompletExport implements WithMultipleSheets
{
    public function __construct(
        private string $debut,
        private string $fin
    ) {}

    public function sheets(): array
    {
        return [
            new VentesExport($this->debut, $this->fin),
            new StockExport(),
            new TopProduitsExport($this->debut, $this->fin),
        ];
    }
}
