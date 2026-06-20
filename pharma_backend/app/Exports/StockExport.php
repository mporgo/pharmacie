<?php
// app/Exports/StockExport.php
namespace App\Exports;

use App\Models\Medicament;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithConditionalRows;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StockExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    public function collection()
    {
        return Medicament::with(['categorie', 'fournisseur'])
            ->actif()
            ->orderBy('nom')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Médicament',
            'Catégorie',
            'Forme',
            'Fournisseur',
            'Prix achat (FCFA)',
            'Prix vente (FCFA)',
            'Stock actuel',
            'Stock minimum',
            'Statut stock',
            'Date expiration',
        ];
    }

    public function map($med): array
    {
        // Statut stock
        if ($med->stock_actuel === 0) {
            $statut = 'RUPTURE';
        } elseif ($med->stock_actuel <= $med->stock_minimum) {
            $statut = 'FAIBLE';
        } else {
            $statut = 'OK';
        }

        // Expiration
        $expiration = '—';
        if ($med->date_expiration) {
            $diff = now()->diffInDays($med->date_expiration, false);
            if ($diff < 0)       $expiration = 'EXPIRÉ (' . $med->date_expiration->format('d/m/Y') . ')';
            elseif ($diff < 30)  $expiration = 'BIENTÔT (' . $med->date_expiration->format('d/m/Y') . ')';
            else                 $expiration = $med->date_expiration->format('d/m/Y');
        }

        return [
            $med->nom,
            $med->categorie?->nom ?? '—',
            $med->forme ?? '—',
            $med->fournisseur?->nom ?? '—',
            number_format($med->prix_achat, 0, ',', ' '),
            number_format($med->prix_vente, 0, ',', ' '),
            $med->stock_actuel,
            $med->stock_minimum,
            $statut,
            $expiration,
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
        return 'Stock';
    }
}
