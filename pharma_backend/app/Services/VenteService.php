<?php
namespace App\Services;

use App\Models\Vente;
use App\Models\VenteDetail;
use App\Models\MouvementStock;
use Illuminate\Support\Facades\DB;

class VenteService
{
    public function creerVente(array $data, int $userId): Vente
    {
        return DB::transaction(function () use ($data, $userId) {
            // Créer la vente
            $vente = Vente::create([
                'reference'    => Vente::genererReference(),
                'user_id'      => $userId,
                'total'        => $data['total'],
                'remise'       => $data['remise'] ?? 0,
                'montant_paye' => $data['montant_paye'],
                'monnaie' => $data['montant_paye'] - ($data['total'] - ($data['remise'] ?? 0)),
                'client_nom'   => $data['client_nom'] ?? null,
            ]);

            // Traiter les détails
            foreach ($data['details'] as $item) {
                $medicament = \App\Models\Medicament::findOrFail($item['medicament_id']);

                if ($medicament->stock_actuel < $item['quantite']) {
                    throw new \Exception("Stock insuffisant pour : {$medicament->nom}");
                }

                VenteDetail::create([
                    'vente_id'      => $vente->id,
                    'medicament_id' => $item['medicament_id'],
                    'quantite'      => $item['quantite'],
                    'prix_unitaire' => $medicament->prix_vente,
                    'sous_total'    => $medicament->prix_vente * $item['quantite'],
                ]);

                // Déduire du stock + mouvement
                $stockAvant = $medicament->stock_actuel;
                $medicament->decrement('stock_actuel', $item['quantite']);

                MouvementStock::create([
                    'medicament_id' => $item['medicament_id'],
                    'type'          => 'sortie',
                    'quantite'      => $item['quantite'],
                    'stock_avant'   => $stockAvant,
                    'stock_apres'   => $stockAvant - $item['quantite'],
                    'reference'     => $vente->reference,
                    'motif'         => 'Vente',
                    'user_id'       => $userId,
                ]);
            }

            return $vente->load('details.medicament', 'user');
        });
    }
}
