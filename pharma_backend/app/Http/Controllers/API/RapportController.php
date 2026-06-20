<?php
// app/Http/Controllers/API/RapportController.php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\{Vente, Medicament, VenteDetail};
use App\Exports\{VentesExport, StockExport, RapportCompletExport};
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RapportController extends Controller
{
    // ─── Dashboard ────────────────────────────────────────────
    public function dashboard()
    {
        $today = now()->toDateString();

        $ventesJour = Vente::whereDate('created_at', $today)
            ->where('statut', 'validee')
            ->selectRaw('COUNT(*) as nombre, SUM(total) as chiffre_affaires')
            ->first();

        $stockFaible   = Medicament::actif()->stockFaible()->count();
        $expires       = Medicament::actif()->expire()->count();
        $expireBientot = Medicament::actif()->expireBientot(30)->count();

        $ca7jours = Vente::where('statut', 'validee')
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topProduits = VenteDetail::with('medicament:id,nom')
            ->whereHas('vente', fn($q) => $q
                ->where('statut', 'validee')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
            )
            ->selectRaw('medicament_id, SUM(quantite) as total_vendu, SUM(sous_total) as ca')
            ->groupBy('medicament_id')
            ->orderByDesc('total_vendu')
            ->limit(5)
            ->get();

        return response()->json([
            'ventes_jour'    => $ventesJour,
            'stock_faible'   => $stockFaible,
            'expires'        => $expires,
            'expire_bientot' => $expireBientot,
            'ca_7jours'      => $ca7jours,
            'top_produits'   => $topProduits,
        ]);
    }

    // ─── Ventes par période ────────────────────────────────────
    public function ventes(Request $request)
    {
        $request->validate([
            'debut' => 'required|date',
            'fin'   => 'required|date|after_or_equal:debut',
        ]);

        $ventes = Vente::where('statut', 'validee')
            ->whereBetween(DB::raw('DATE(created_at)'), [$request->debut, $request->fin])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as nombre, SUM(total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($ventes);
    }

    // ─── Top produits ─────────────────────────────────────────
    public function topProduits(Request $request)
    {
        $limit = $request->get('limit', 10);

        $query = VenteDetail::with('medicament:id,nom')
            ->whereHas('vente', fn($q) => $q->where('statut', 'validee'));

        if ($request->filled('debut') && $request->filled('fin')) {
            $query->whereHas('vente', fn($q) => $q
                ->whereBetween(DB::raw('DATE(created_at)'), [$request->debut, $request->fin])
            );
        }

        $produits = $query
            ->selectRaw('medicament_id, SUM(quantite) as total_vendu, SUM(sous_total) as ca')
            ->groupBy('medicament_id')
            ->orderByDesc('total_vendu')
            ->limit($limit)
            ->get();

        return response()->json($produits);
    }

    // ─── Export PDF rapport ventes ─────────────────────────────
    public function exportPdf(Request $request)
    {
        $request->validate([
            'debut' => 'required|date',
            'fin'   => 'required|date|after_or_equal:debut',
        ]);

        // Données
        $ventesParJour = Vente::where('statut', 'validee')
            ->whereBetween(DB::raw('DATE(created_at)'), [$request->debut, $request->fin])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as nombre, SUM(total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topProduits = VenteDetail::with('medicament:id,nom')
            ->whereHas('vente', fn($q) => $q
                ->where('statut', 'validee')
                ->whereBetween(DB::raw('DATE(created_at)'), [$request->debut, $request->fin])
            )
            ->selectRaw('medicament_id, SUM(quantite) as total_vendu, SUM(sous_total) as ca')
            ->groupBy('medicament_id')
            ->orderByDesc('total_vendu')
            ->limit(10)
            ->get();

        $totalVentes = $ventesParJour->sum('nombre');
        $totalCA     = $ventesParJour->sum('total');

        $pdf = Pdf::loadView('pdf.rapport-ventes', compact(
            'ventesParJour',
            'topProduits',
            'totalVentes',
            'totalCA',
        ) + [
            'debut' => $request->debut,
            'fin'   => $request->fin,
        ]);

        $pdf->setPaper('A4', 'portrait');

        $filename = 'rapport-ventes-' . $request->debut . '-au-' . $request->fin . '.pdf';

        return $pdf->download($filename);
    }

    // ─── Export PDF stock ──────────────────────────────────────
    public function exportPdfStock()
    {
        $medicaments = Medicament::with(['categorie'])
            ->actif()
            ->orderBy('nom')
            ->get();

        $nbRupture = $medicaments->where('stock_actuel', 0)->count();
        $nbFaible  = $medicaments
            ->filter(fn($m) => $m->stock_actuel > 0 && $m->stock_actuel <= $m->stock_minimum)
            ->count();
        $nbExpires = $medicaments
            ->filter(fn($m) => $m->date_expiration && $m->date_expiration <= now()->addDays(30))
            ->count();

        $pdf = Pdf::loadView('pdf.rapport-stock', compact(
            'medicaments', 'nbRupture', 'nbFaible', 'nbExpires'
        ));

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('rapport-stock-' . now()->format('Y-m-d') . '.pdf');
    }

    // ─── Export Excel ventes ───────────────────────────────────
    public function exportExcel(Request $request)
    {
        $request->validate([
            'debut' => 'required|date',
            'fin'   => 'required|date|after_or_equal:debut',
        ]);

        $filename = 'rapport-ventes-' . $request->debut . '-au-' . $request->fin . '.xlsx';

        return Excel::download(
            new VentesExport($request->debut, $request->fin),
            $filename
        );
    }

    // ─── Export Excel complet (multi-feuilles) ─────────────────
    public function exportExcelComplet(Request $request)
    {
        $request->validate([
            'debut' => 'required|date',
            'fin'   => 'required|date|after_or_equal:debut',
        ]);

        $filename = 'rapport-complet-' . $request->debut . '-au-' . $request->fin . '.xlsx';

        return Excel::download(
            new RapportCompletExport($request->debut, $request->fin),
            $filename
        );
    }

    // ─── Export Excel stock ────────────────────────────────────
    public function exportExcelStock()
    {
        return Excel::download(
            new StockExport(),
            'stock-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
