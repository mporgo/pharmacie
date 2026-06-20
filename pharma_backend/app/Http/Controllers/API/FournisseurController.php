<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Fournisseur;
use Illuminate\Http\Request;

class FournisseurController extends Controller
{
    public function index()
    {
        $fournisseurs = Fournisseur::withCount('achats')
            ->orderBy('nom')
            ->get();

        return response()->json($fournisseurs);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'       => 'required|string|max:255',
            'telephone' => 'nullable|string|max:30',
            'email'     => 'nullable|email|max:255',
            'adresse'   => 'nullable|string|max:500',
        ]);

        $fournisseur = Fournisseur::create($request->all());
        return response()->json($fournisseur, 201);
    }

    public function show(Fournisseur $fournisseur)
    {
        $fournisseur->load([
            'achats' => fn($q) => $q
                ->with('details.medicament:id,nom')
                ->latest()
                ->limit(10),
        ]);

        return response()->json($fournisseur);
    }

    public function update(Request $request, Fournisseur $fournisseur)
    {
        $request->validate([
            'nom'       => 'sometimes|string|max:255',
            'telephone' => 'nullable|string|max:30',
            'email'     => 'nullable|email|max:255',
            'adresse'   => 'nullable|string|max:500',
        ]);

        $fournisseur->update($request->all());
        return response()->json($fournisseur);
    }

    public function destroy(Fournisseur $fournisseur)
    {
        // Vérifier qu'il n'a pas de commandes actives
        if ($fournisseur->achats()->where('statut', 'commande')->exists()) {
            return response()->json([
                'message' => 'Ce fournisseur a des commandes en cours. Impossible de le supprimer.'
            ], 422);
        }

        $fournisseur->delete();
        return response()->json(['message' => 'Fournisseur supprimé.']);
    }
}
