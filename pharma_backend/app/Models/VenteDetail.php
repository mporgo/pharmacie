<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VenteDetail extends Model
{
    protected $fillable = [
        'vente_id',
        'medicament_id',
        'quantite',
        'prix_unitaire',
        'sous_total',
    ];

    protected $casts = [
        'prix_unitaire' => 'float',
        'sous_total'    => 'float',
        'quantite'      => 'integer',
    ];

    // Relations
    public function vente()
    {
        return $this->belongsTo(Vente::class);
    }

    public function medicament()
    {
        return $this->belongsTo(Medicament::class);
    }
}
