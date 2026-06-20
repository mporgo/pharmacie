<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vente extends Model
{
    protected $fillable = [
        'reference', 'user_id', 'total', 'remise',
        'montant_paye', 'monnaie', 'statut', 'client_nom'
    ];

    protected $casts = [
        'total' => 'float',
        'remise' => 'float',
        'montant_paye' => 'float',
        'monnaie' => 'float',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function details() {
        return $this->hasMany(VenteDetail::class);
    }

    // Générer référence automatique
    public static function genererReference(): string {
        $dernier = self::latest()->first();
        $num = $dernier ? (int) substr($dernier->reference, -5) + 1 : 1;
        return 'VTE-' . date('Ymd') . '-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}
