<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VenteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'total'                          => 'required|numeric|min:0',
            'remise'                         => 'nullable|numeric|min:0',
            'montant_paye'                   => 'required|numeric|min:0',
            'client_nom'                     => 'nullable|string|max:255',
            'details'                        => 'required|array|min:1',
            'details.*.medicament_id'        => 'required|exists:medicaments,id',
            'details.*.quantite'             => 'required|integer|min:1',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $total  = $this->total ?? 0;
            $remise = $this->remise ?? 0;
            $paye   = $this->montant_paye ?? 0;

            if ($paye < ($total - $remise)) {
                $v->errors()->add('montant_paye', 'Le montant payé est insuffisant.');
            }
        });
    }
}
