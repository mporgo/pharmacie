<?php
namespace App\Services;

use App\Models\{Achat, AchatDetail, MouvementStock};
use Illuminate\Support\Facades\DB;

class AchatService
{
    // Créer une commande fournisseur
    public function creerCommande(array $data, int $userId): Achat
    {
        return DB::transaction(function () use ($data, $userId) {
            $achat = Achat::create([
                'reference'      => Achat::genererReference(),
                'fournisseur_id' => $data['fournisseur_id'],
                'user_id'        => $userId,
                'total'          => $data['total'],
                'statut'         => 'commande',
                'date_commande'  => now()->toDateString(),
            ]);

            foreach ($data['details'] as $item) {
                AchatDetail::create([
                    'achat_id'      => $achat->id,
                    'medicament_id' => $item['medicament_id'],
                    'quantite'      => $item['quantite'],
                    'prix_unitaire' => $item['prix_unitaire'],
                    'sous_total'    => $item['prix_unitaire'] * $item['quantite'],
                ]);
            }

            return $achat->load('details.medicament', 'fournisseur');
        });
    }

    // Enregistrer la livraison + mise à jour stock automatique
    public function enregistrerLivraison(Achat $achat, int $userId): Achat
    {
        if ($achat->statut !== 'commande') {
            throw new \Exception('Cette commande ne peut plus être réceptionnée.');
        }

        return DB::transaction(function () use ($achat, $userId) {
            // Mettre à jour le stock pour chaque détail
            foreach ($achat->details as $detail) {
                $medicament  = $detail->medicament;
                $stockAvant  = $medicament->stock_actuel;

                $medicament->increment('stock_actuel', $detail->quantite);

                MouvementStock::create([
                    'medicament_id' => $medicament->id,
                    'type'          => 'entree',
                    'quantite'      => $detail->quantite,
                    'stock_avant'   => $stockAvant,
                    'stock_apres'   => $stockAvant + $detail->quantite,
                    'reference'     => $achat->reference,
                    'motif'         => "Livraison commande {$achat->reference}",
                    'user_id'       => $userId,
                ]);
            }

            // Marquer comme livrée
            $achat->update([
                'statut'         => 'livree',
                'date_livraison' => now()->toDateString(),
            ]);

            return $achat->fresh(['details.medicament', 'fournisseur']);
        });
    }
}
