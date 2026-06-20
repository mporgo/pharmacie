<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MedicamentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('medicament')?->id;

        return [
            'nom'             => 'required|string|max:255',
            'dosage'          => 'nullable|string|max:100',
            'forme'           => 'nullable|string|max:100',
            'categorie_id'    => 'required|exists:categories,id',
            'fournisseur_id'  => 'nullable|exists:fournisseurs,id',
            'prix_achat'      => 'required|numeric|min:0',
            'prix_vente'      => 'required|numeric|min:0',
            'stock_actuel'    => 'required|integer|min:0',
            'stock_minimum'   => 'required|integer|min:0',
            'code_barre'      => "nullable|string|unique:medicaments,code_barre,{$id}",
            'date_expiration' => 'nullable|date',
        ];
    }
}
