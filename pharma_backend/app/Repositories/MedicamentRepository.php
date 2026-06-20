<?php
namespace App\Repositories;

use App\Models\Medicament;

class MedicamentRepository
{
    public function getAll(array $filters = [])
    {
        $query = Medicament::with(['categorie:id,nom', 'fournisseur:id,nom'])
            ->actif();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('code_barre', $search)
                  ->orWhere('dosage', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['categorie_id'])) {
            $query->where('categorie_id', $filters['categorie_id']);
        }

        return $query->orderBy('nom')->paginate(15);
    }

    public function find(int $id): Medicament
    {
        return Medicament::with(['categorie', 'fournisseur'])->findOrFail($id);
    }

    public function create(array $data): Medicament
    {
        return Medicament::create($data);
    }

    public function update(Medicament $medicament, array $data): Medicament
    {
        $medicament->update($data);
        return $medicament->fresh(['categorie', 'fournisseur']);
    }
    
    public function getAlertes(): array
    {
        return [
            'stock_faible'      => Medicament::actif()->stockFaible()->count(),
            'expires'           => Medicament::actif()->expire()->count(),
            'expire_bientot'    => Medicament::actif()->expireBientot()->count(),
        ];
    }
}
