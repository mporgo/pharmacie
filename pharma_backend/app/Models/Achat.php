<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Achat extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'fournisseur_id',
        'user_id',
        'total',
        'statut',
        'date_commande',
        'date_livraison',
    ];

    protected $casts = [
        'total'          => 'float',
        'date_commande'  => 'date',
        'date_livraison' => 'date',
    ];

    // Relations
    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(AchatDetail::class);
    }

    // Générer référence automatique
    public static function genererReference(): string
    {
        $dernier = self::latest()->first();
        $num = $dernier ? (int) substr($dernier->reference, -5) + 1 : 1;
        return 'ACH-' . date('Ymd') . '-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    // Scopes
    public function scopeEnCours($query)
    {
        return $query->where('statut', 'commande');
    }

    public function scopeLivrees($query)
    {
        return $query->where('statut', 'livree');
    }
}
