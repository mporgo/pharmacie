<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AchatDetail extends Model
{
    protected $fillable = [
        'achat_id',
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
    public function achat()
    {
        return $this->belongsTo(Achat::class);
    }

    public function medicament()
    {
        return $this->belongsTo(Medicament::class);
    }
}
