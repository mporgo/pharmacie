<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\VenteService;
use App\Http\Requests\VenteRequest;
use App\Models\Vente;
use Illuminate\Http\Request;

class VenteController extends Controller
{
    public function __construct(private VenteService $service) {}

    public function index(Request $request)
    {
        $ventes = Vente::with(['user', 'details.medicament'])
            ->when($request->date, fn($q) => $q->whereDate('created_at', $request->date))
            ->when($request->statut, fn($q) => $q->where('statut', $request->statut))
            ->latest()
            ->paginate(20);

        return response()->json($ventes);
    }

    public function store(VenteRequest $request)
    {
        try {
            $vente = $this->service->creerVente(
                $request->validated(),
                $request->user()->id
            );
            return response()->json($vente, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Vente $vente)
    {
        return response()->json($vente->load('details.medicament', 'user'));
    }

    public function destroy(Vente $vente)
    {
        // Annulation seulement (pas suppression physique)
        $vente->update(['statut' => 'annulee']);
        return response()->json(['message' => 'Vente annulée.']);
    }
}
