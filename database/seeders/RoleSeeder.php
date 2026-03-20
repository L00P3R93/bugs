<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->warn(PHP_EOL.'Creating Roles and Permissions...');

        // Define Permissions
        $permissions = [
            'view_accounts', 'view_account', 'create_account', 'update_account', 'delete_account',
            'view_games', 'view_game', 'create_game', 'update_game', 'delete_game',
            'view_played_games', 'view_played_game', 'create_played_game', 'update_played_game', 'delete_played_game',

            'view_categories', 'view_category', 'create_category', 'update_category', 'delete_category',
            'view_severities', 'view_severity', 'create_severity', 'update_severity', 'delete_severity',
            'view_labels', 'view_label', 'create_label', 'update_label', 'delete_label',

            'view_bugs', 'view_bug', 'create_bug', 'update_bug', 'delete_bug',
            'view_reviews', 'view_review', 'create_review', 'update_review', 'delete_review',

            'view_comments', 'view_comment', 'create_comment', 'update_comment', 'delete_comment',
            'view_wallets', 'view_wallet', 'create_wallet', 'update_wallet', 'delete_wallet',
            'view_transactions', 'view_transaction', 'create_transaction', 'update_transaction', 'delete_transaction',

            'view_users', 'view_user', 'create_user', 'update_user', 'delete_user',
            'view_roles', 'view_role', 'create_role', 'update_role', 'delete_role',
            'view_permissions', 'view_permission', 'create_permission', 'update_permission', 'delete_permission',
            'view_audit_logs',
        ];

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission]);
        }

        // Define Roles with Permissions
        $roles = [
            'Super Admin' => $permissions,
            'Admin' => $permissions,
            'Player' => [
                'view_played_games', 'view_played_game',
                'view_bugs', 'view_bug', 'create_bug', 'update_bug',
                'view_reviews', 'view_review',
                'view_comments', 'view_comment', 'create_comment',
                'view_transactions', 'view_transaction', 'create_transaction', 'update_transaction',
            ],
            'Tester' => [
                'view_played_games', 'view_played_game',
                'view_bugs', 'view_bug', 'create_bug', 'update_bug',
                'view_reviews', 'view_review',
                'view_comments', 'view_comment', 'create_comment',
                'view_transactions', 'view_transaction', 'create_transaction', 'update_transaction',
            ],
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::query()->firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($perms);
        }

        $this->command?->info('Roles and permissions seeded successfully.');
    }
}
