<?php
// app/Exports/VentesExport.php
namespace App\Exports;

use App\Models\Vente;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class VentesExport implements
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
        return Vente::with(['user', 'details.medicament'])
            ->where('statut', 'validee')
            ->whereBetween(\DB::raw('DATE(created_at)'), [$this->debut, $this->fin])
            ->orderBy('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Référence',
            'Date',
            'Caissier',
            'Client',
            'Nb articles',
            'Remise (FCFA)',
            'Total (FCFA)',
            'Montant payé (FCFA)',
            'Monnaie (FCFA)',
            'Statut',
        ];
    }

    public function map($vente): array
    {
        return [
            $vente->reference,
            $vente->created_at->format('d/m/Y H:i'),
            $vente->user?->name ?? '—',
            $vente->client_nom ?? '—',
            $vente->details->count(),
            number_format($vente->remise, 0, ',', ' '),
            number_format($vente->total, 0, ',', ' '),
            number_format($vente->montant_paye, 0, ',', ' '),
            number_format($vente->monnaie, 0, ',', ' '),
            $vente->statut,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // En-tête : fond bleu foncé, texte blanc, gras
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1A3C6B'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function title(): string
    {
        return 'Ventes';
    }
}
