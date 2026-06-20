<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Achat;
use App\Services\AchatService;
use Illuminate\Http\Request;

class AchatController extends Controller
{
    public function __construct(private AchatService $service) {}

    public function index(Request $request)
    {
        $achats = Achat::with(['fournisseur', 'user', 'details'])
            ->when($request->statut, fn($q) => $q->where('statut', $request->statut))
            ->latest()
            ->paginate(15);

        return response()->json($achats);
    }

    public function store(Request $request)
    {
        $request->validate([
            'fournisseur_id'              => 'required|exists:fournisseurs,id',
            'total'                       => 'required|numeric|min:0',
            'details'                     => 'required|array|min:1',
            'details.*.medicament_id'     => 'required|exists:medicaments,id',
            'details.*.quantite'          => 'required|integer|min:1',
            'details.*.prix_unitaire'     => 'required|numeric|min:0',
        ]);

        try {
            $achat = $this->service->creerCommande(
                $request->all(),
                $request->user()->id
            );
            return response()->json($achat, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Achat $achat)
    {
        return response()->json(
            $achat->load('details.medicament', 'fournisseur', 'user')
        );
    }

    // Réceptionner une livraison
    public function livraison(Request $request, Achat $achat)
    {
        try {
            $result = $this->service->enregistrerLivraison($achat, $request->user()->id);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(Achat $achat)
    {
        if ($achat->statut === 'livree') {
            return response()->json(['message' => 'Impossible d\'annuler une commande livrée.'], 422);
        }
        $achat->update(['statut' => 'annulee']);
        return response()->json(['message' => 'Commande annulée.']);
    }
}
