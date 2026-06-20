<?php
// app/Policies/MedicamentPolicy.php
namespace App\Policies;

use App\Models\{User, Medicament};

class MedicamentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('medicaments.view');
    }

    public function view(User $user, Medicament $medicament): bool
    {
        return $user->hasPermissionTo('medicaments.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('medicaments.create');
    }

    public function update(User $user, Medicament $medicament): bool
    {
        return $user->hasPermissionTo('medicaments.edit');
    }

    public function delete(User $user, Medicament $medicament): bool
    {
        return $user->hasPermissionTo('medicaments.delete');
    }
}
