<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Categorie extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'description'];

    // Relations
    public function medicaments()
    {
        return $this->hasMany(Medicament::class);
    }

    // Accesseur : nombre de médicaments actifs
    public function getMedicamentsActifsCountAttribute(): int
    {
        return $this->medicaments()->where('actif', true)->count();
    }
}
