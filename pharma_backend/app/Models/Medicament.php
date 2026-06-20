<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Medicament extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom', 'dosage', 'forme', 'categorie_id', 'fournisseur_id',
        'prix_achat', 'prix_vente', 'stock_actuel', 'stock_minimum',
        'code_barre', 'date_expiration', 'actif'
    ];

    protected $casts = [
        'date_expiration' => 'date',
        'actif' => 'boolean',
        'prix_achat' => 'float',
        'prix_vente' => 'float',
    ];

    // Relations
    public function categorie() {
        return $this->belongsTo(Categorie::class);
    }

    public function fournisseur() {
        return $this->belongsTo(Fournisseur::class);
    }

    public function mouvements() {
        return $this->hasMany(MouvementStock::class);
    }

    // Scopes
    public function scopeActif($query) {
        return $query->where('actif', true);
    }

    public function scopeStockFaible($query) {
        return $query->whereRaw('stock_actuel <= stock_minimum');
    }

    public function scopeExpireBientot($query, $jours = 30) {
        return $query->whereBetween('date_expiration', [now(), now()->addDays($jours)]);
    }

    public function scopeExpire($query) {
        return $query->where('date_expiration', '<', now());
    }
}
