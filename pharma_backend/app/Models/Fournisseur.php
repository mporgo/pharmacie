<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fournisseur extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'telephone',
        'email',
        'adresse',
    ];

    // Relations
    public function medicaments()
    {
        return $this->hasMany(Medicament::class);
    }

    public function achats()
    {
        return $this->hasMany(Achat::class);
    }

    // Total achats passés avec ce fournisseur
    public function getTotalAchatsAttribute(): float
    {
        return $this->achats()
            ->where('statut', 'livree')
            ->sum('total');
    }
}
