<?php
// app/Exports/TopProduitsExport.php
namespace App\Exports;

use App\Models\VenteDetail;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TopProduitsExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    public function __construct(
        private string $debut,
        private string $fin
    ) {}

    public function collection()
    {
        return VenteDetail::with('medicament:id,nom')
            ->whereHas('vente', fn($q) => $q
                ->where('statut', 'validee')
                ->whereBetween(DB::raw('DATE(created_at)'), [$this->debut, $this->fin])
            )
            ->selectRaw('medicament_id, SUM(quantite) as total_vendu, SUM(sous_total) as ca')
            ->groupBy('medicament_id')
            ->orderByDesc('total_vendu')
            ->limit(20)
            ->get();
    }

    public function headings(): array
    {
        return ['Médicament', 'Quantité vendue', 'CA généré (FCFA)'];
    }

    public function map($row): array
    {
        return [
            $row->medicament?->nom ?? '—',
            $row->total_vendu,
            number_format($row->ca, 0, ',', ' '),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1A3C6B'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Top Produits';
    }
}
