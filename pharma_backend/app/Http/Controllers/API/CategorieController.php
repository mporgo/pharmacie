<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    public function index()
    {
        $categories = Categorie::withCount([
            'medicaments as total_medicaments' => fn($q) => $q->where('actif', true)
        ])->orderBy('nom')->get();

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'         => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string|max:500',
        ]);

        return response()->json(Categorie::create($request->all()), 201);
    }

    public function update(Request $request, Categorie $categorie)
    {
        $request->validate([
            'nom'         => "sometimes|string|max:255|unique:categories,nom,{$categorie->id}",
            'description' => 'nullable|string|max:500',
        ]);

        $categorie->update($request->all());
        return response()->json($categorie);
    }

    public function destroy(Categorie $categorie)
    {
        if ($categorie->medicaments()->where('actif', true)->exists()) {
            return response()->json([
                'message' => 'Cette catégorie contient des médicaments actifs.'
            ], 422);
        }

        $categorie->delete();
        return response()->json(['message' => 'Catégorie supprimée.']);
    }
}
