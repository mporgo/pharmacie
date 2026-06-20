<?php
// database/seeders/RolePermissionSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Permissions
       /*  $permissions = [
            'medicaments.view', 'medicaments.create', 'medicaments.edit', 'medicaments.delete',
            'ventes.view', 'ventes.create', 'ventes.delete',
            'achats.view', 'achats.create', 'achats.edit',
            'stock.view', 'stock.manage',
            'fournisseurs.view', 'fournisseurs.manage',
            'users.view', 'users.manage',
            'rapports.view',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Rôles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $pharmacien = Role::firstOrCreate(['name' => 'pharmacien']);
        $caissier = Role::firstOrCreate(['name' => 'caissier']);

        // Attribution permissions
        $admin->givePermissionTo(Permission::all());

        $pharmacien->givePermissionTo([
            'medicaments.view', 'medicaments.create', 'medicaments.edit',
            'ventes.view', 'ventes.create',
            'achats.view', 'achats.create',
            'stock.view', 'stock.manage',
            'fournisseurs.view', 'fournisseurs.manage',
            'rapports.view',
        ]);

        $caissier->givePermissionTo([
            'ventes.view', 'ventes.create',
            'medicaments.view',
            'stock.view',
        ]);

        // Créer un admin par défaut
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@pharma.com'],
            [
                'name'     => 'Administrateur',
                'password' => bcrypt('Admin@1234'),
                'actif'    => true,
            ]
        );
        $adminUser->assignRole('admin'); */

        //créer une categorie
        /* $categorie = \App\Models\Categorie::firstOrCreate([
            'nom' => 'Anti-inflammatoires',
        ]); */
    }
}
