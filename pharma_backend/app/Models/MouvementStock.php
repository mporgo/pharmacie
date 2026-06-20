<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MouvementStock extends Model
{
    protected $fillable = [
        'medicament_id',
        'type',
        'quantite',
        'stock_avant',
        'stock_apres',
        'reference',
        'motif',
        'user_id',
    ];

    protected $casts = [
        'quantite'    => 'integer',
        'stock_avant' => 'integer',
        'stock_apres' => 'integer',
    ];

    // Relations
    public function medicament()
    {
        return $this->belongsTo(Medicament::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeEntrees($query)
    {
        return $query->where('type', 'entree');
    }

    public function scopeSorties($query)
    {
        return $query->where('type', 'sortie');
    }

    public function scopePeriode($query, string $debut, string $fin)
    {
        return $query->whereBetween('created_at', [$debut, $fin]);
    }
}
