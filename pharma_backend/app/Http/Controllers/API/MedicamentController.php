<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\MedicamentRepository;
use App\Http\Requests\MedicamentRequest;
use App\Models\Medicament;
use Illuminate\Http\Request;

class MedicamentController extends Controller
{
    public function __construct(private MedicamentRepository $repo) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Medicament::class);

        $data = $this->repo->getAll($request->only(['search', 'categorie_id']));
        return response()->json($data);
    }

    public function store(MedicamentRequest $request)
    {
        $this->authorize('create', Medicament::class);

        $medicament = $this->repo->create($request->validated());
        return response()->json($medicament->load('categorie', 'fournisseur'), 201);
    }

    public function show(Medicament $medicament)
    {
        return response()->json($medicament->load('categorie', 'fournisseur', 'mouvements'));
    }

    public function update(MedicamentRequest $request, Medicament $medicament)
    {
        $this->authorize('update', $medicament);

        $updated = $this->repo->update($medicament, $request->validated());
        return response()->json($updated->load('categorie', 'fournisseur'));
    }

    public function destroy(Medicament $medicament)
    {
        $this->authorize('delete', $medicament);

        $medicament->update(['actif' => false]);
        return response()->json(['message' => 'Médicament désactivé.']);
    }
}
