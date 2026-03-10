<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User permissions
            'view profile', 'edit profile',
            'view orders', 'create orders', 'cancel orders',
            'view wishlist', 'manage wishlist',
            'view cart', 'manage cart',
            'write reviews',

            // Seller permissions
            'manage products', 'view seller orders', 'manage seller orders',
            'view seller analytics', 'manage seller profile',
            'manage inventory', 'view payouts',

            // Delivery permissions
            'view deliveries', 'accept deliveries', 'update delivery status',
            'view delivery route',

            // Admin permissions
            'manage users', 'manage sellers', 'manage delivery partners',
            'manage all orders', 'manage categories', 'manage settings',
            'view admin dashboard', 'manage roles', 'manage permissions',
            'manage blog', 'view analytics',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $user = Role::firstOrCreate(['name' => 'user']);
        $user->givePermissionTo([
            'view profile', 'edit profile',
            'view orders', 'create orders', 'cancel orders',
            'view wishlist', 'manage wishlist',
            'view cart', 'manage cart',
            'write reviews',
        ]);

        $seller = Role::firstOrCreate(['name' => 'seller']);
        $seller->givePermissionTo([
            'view profile', 'edit profile',
            'manage products', 'view seller orders', 'manage seller orders',
            'view seller analytics', 'manage seller profile',
            'manage inventory', 'view payouts',
        ]);

        $deliveryPartner = Role::firstOrCreate(['name' => 'delivery_partner']);
        $deliveryPartner->givePermissionTo([
            'view profile', 'edit profile',
            'view deliveries', 'accept deliveries', 'update delivery status',
            'view delivery route',
        ]);
    }
}
