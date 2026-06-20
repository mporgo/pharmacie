<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\{Medicament, MouvementStock};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = Medicament::with('categorie:id,nom')
            ->actif();

        // ✅ Ajout du filtre search manquant
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                ->orWhere('code_barre', $search);
            });
        }

        $medicaments = $query->orderBy('nom')->paginate(20);
        return response()->json($medicaments);
    }
    
    public function mouvement(Request $request)
    {
        $request->validate([
            'medicament_id' => 'required|exists:medicaments,id',
            'type'          => 'required|in:entree,sortie,inventaire',
            'quantite'      => 'required|integer|min:1',
            'motif'         => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($request) {
            $medicament = Medicament::findOrFail($request->medicament_id);
            $stockAvant = $medicament->stock_actuel;

            if ($request->type === 'entree') {
                $medicament->increment('stock_actuel', $request->quantite);
            } elseif ($request->type === 'sortie') {
                if ($medicament->stock_actuel < $request->quantite) {
                    return response()->json(['message' => 'Stock insuffisant.'], 422);
                }
                $medicament->decrement('stock_actuel', $request->quantite);
            } elseif ($request->type === 'inventaire') {
                $medicament->update(['stock_actuel' => $request->quantite]);
            }

            $mouvement = MouvementStock::create([
                'medicament_id' => $medicament->id,
                'type'          => $request->type,
                'quantite'      => $request->quantite,
                'stock_avant'   => $stockAvant,
                'stock_apres'   => $medicament->fresh()->stock_actuel,
                'motif'         => $request->motif,
                'user_id'       => $request->user()->id,
            ]);

            return response()->json($mouvement->load('medicament'), 201);
        });
    }

    public function mouvements(Request $request)
    {
        $mouvements = MouvementStock::with(['medicament', 'user'])
            ->when($request->medicament_id, fn($q) => $q->where('medicament_id', $request->medicament_id))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate(20);

        return response()->json($mouvements);
    }
}
