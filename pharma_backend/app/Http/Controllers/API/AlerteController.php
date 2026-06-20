<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Medicament;

class AlerteController extends Controller
{
    public function index()
    {
        $stockFaible = Medicament::actif()->stockFaible()
            ->with('categorie:id,nom')
            ->select('id', 'nom', 'stock_actuel', 'stock_minimum', 'categorie_id')
            ->get();

        $expires = Medicament::actif()->expire()
            ->with('categorie:id,nom')
            ->select('id', 'nom', 'stock_actuel', 'date_expiration', 'categorie_id')
            ->get();

        $expireBientot = Medicament::actif()->expireBientot(30)
            ->with('categorie:id,nom')
            ->select('id', 'nom', 'stock_actuel', 'date_expiration', 'categorie_id')
            ->get();

        return response()->json([
            'stock_faible'   => $stockFaible,
            'expires'        => $expires,
            'expire_bientot' => $expireBientot,
            'totaux' => [
                'stock_faible'   => $stockFaible->count(),
                'expires'        => $expires->count(),
                'expire_bientot' => $expireBientot->count(),
            ]
        ]);
    }
}
