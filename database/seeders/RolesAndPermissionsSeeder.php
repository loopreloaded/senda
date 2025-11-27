<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        $permisos = [
            // Permisos del sistema base
            'admin.home',
            'admin.users.index',
            'admin.users.create',
            'admin.users.edit',
            'admin.roles.index',
            'admin.permissions.index',

            // Permisos de facturación
            'ver facturas',
            'crear facturas',
            'aprobar facturas',
            'enviar afip',

            // Permisos de órdenes
            'ver ordenes',
            'crear ordenes',

            // Permisos de reportes
            'ver reportes',

            // Otros
            'administrar usuarios',
            'administrar roles',
            'administrar permisos',
            'configurar empresa',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // Crear roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $ingeniero = Role::firstOrCreate(['name' => 'ingeniero']);
        $secretaria = Role::firstOrCreate(['name' => 'secretaria']);

        // Asignar permisos
        $admin->syncPermissions(Permission::all());

        $ingeniero->syncPermissions([
            'ver facturas',
            'crear facturas',
            'aprobar facturas',
            'ver ordenes',
            'ver reportes',
        ]);

        $secretaria->syncPermissions([
            'ver facturas',
            'crear facturas',
            'enviar afip',
            'ver ordenes',
            'crear ordenes',
        ]);
    }
}
