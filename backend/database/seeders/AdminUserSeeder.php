<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\UsuarioCuenta;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permisos si no existen
        $permissions = [
            'admin.dashboard',
            'admin.users.view',
            'admin.users.create',
            'admin.users.edit',
            'admin.users.delete',
            'admin.levels.view',
            'admin.levels.edit',
            'admin.stats.view',
            'admin.notifications.send',
            'admin.system.settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear roles si no existen
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Asignar todos los permisos al rol admin
        $adminRole->syncPermissions($permissions);

        // Crear usuario administrador
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@tidy.com'],
            [
                'name' => 'Administrador',
                'last_name' => 'Sistema',
                'email' => 'admin@tidy.com',
                'phone' => '+1234567890',
                'password' => Hash::make('TidyAdmin2024!'),
                'email_verified_at' => now(),
            ]
        );

        // Asignar rol de administrador
        $adminUser->assignRole($adminRole);

        // Crear UsuarioCuenta asociado
        $usuarioCuenta = UsuarioCuenta::firstOrCreate(
            ['user_id' => $adminUser->id],
            [
                'user_id' => $adminUser->id,
                'total_xp' => 10000,
                'current_level' => 5, // Nivel experto
                'configuraciones' => json_encode([
                    'theme' => 'light',
                    'language' => 'es',
                    'notifications' => true,
                    'email_notifications' => true,
                    'push_notifications' => true,
                ]),
                'is_premium' => true,
                'premium_expires_at' => now()->addYear(),
            ]
        );

        // Crear entrada en tabla admin usando DB directamente
        DB::table('admin')->updateOrInsert(
            ['usuario_id' => $adminUser->id],
            [
                'usuario_id' => $adminUser->id,
                'rol_id' => 1,
                'rol_nombre' => 'Super Administrador',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info('✅ Usuario administrador creado exitosamente:');
        $this->command->info('   Email: admin@tidy.com');
        $this->command->info('   Password: TidyAdmin2024!');
        $this->command->info('   Rol: Administrador del Sistema');

        // Crear algunos usuarios de prueba
        $this->createTestUsers($userRole);
    }

    /**
     * Crear usuarios de prueba
     */
    private function createTestUsers(Role $userRole): void
    {
        $testUsers = [
            [
                'name' => 'Usuario',
                'last_name' => 'Demo',
                'email' => 'demo@tidy.com',
                'password' => 'Demo2024!',
                'nivel' => 3,
                'experiencia' => 2500,
                'premium' => false,
            ],
            [
                'name' => 'María',
                'last_name' => 'García',
                'email' => 'maria@tidy.com',
                'password' => 'Maria2024!',
                'nivel' => 4,
                'experiencia' => 5000,
                'premium' => true,
            ],
            [
                'name' => 'Juan',
                'last_name' => 'Pérez',
                'email' => 'juan@tidy.com',
                'password' => 'Juan2024!',
                'nivel' => 2,
                'experiencia' => 1200,
                'premium' => false,
            ],
        ];

        foreach ($testUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'last_name' => $userData['last_name'],
                    'email' => $userData['email'],
                    'phone' => '+521234567' . rand(100, 999),
                    'password' => Hash::make($userData['password']),
                    'email_verified_at' => now(),
                ]
            );

            // Asignar rol de usuario
            $user->assignRole($userRole);

            // Crear UsuarioCuenta
            UsuarioCuenta::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'user_id' => $user->id,
                    'total_xp' => $userData['experiencia'],
                    'current_level' => $userData['nivel'],
                    'configuraciones' => json_encode([
                        'theme' => rand(0, 1) ? 'light' : 'dark',
                        'language' => collect(['es', 'en', 'pt'])->random(),
                        'notifications' => true,
                        'email_notifications' => rand(0, 1),
                        'push_notifications' => rand(0, 1),
                    ]),
                    'is_premium' => $userData['premium'],
                    'premium_expires_at' => $userData['premium'] ? now()->addMonths(6) : null,
                ]
            );
        }

        $this->command->info('✅ Usuarios de prueba creados:');
        foreach ($testUsers as $userData) {
            $this->command->info("   - {$userData['email']} (Password: {$userData['password']})");
        }
    }
}
