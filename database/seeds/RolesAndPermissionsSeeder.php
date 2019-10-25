<?php

use App\Permission;
use App\Role;
use App\User;
use App\Utils\Utils;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;
use Faker\Factory as Faker;

class RolesAndPermissionsSeeder extends Seeder
{
    protected $created_users;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Ask for db migration refresh, default is no
        if ($this->command->confirm('Do you wish to refresh migration before seeding, it will clear all old data ? [y|N]', false)) {
            // Call the php artisan migrate:refresh
            $this->command->call('migrate:refresh');
            $this->command->warn("Data cleared, starting from blank database.");
        }

        $go_ahead = $this->command->confirm('Do you wish to FORCE every operation without prompt? [Y|n]', true);

        // Seed the default permissions
        $roles = Role::defaultRoles();

        foreach ($roles as $role_name => $role_data) {
            if ($go_ahead || $this->command->confirm("Do you wish to create ROLE '{$role_data['label']}'? [Y|n]", true)) {
                $role = Role::firstOrCreate(['name' => trim($role_name)]);

                if ($go_ahead || $this->command->confirm('Associate PERMISSIONS to this ROLE? [Y|n]', true)) {

                    // add roles
                    foreach ($role_data['permissions'] as $permission_name) {

                        if ($go_ahead || $this->command->confirm("Do you wish to add PERMISSION '$permission_name' to ROLE '$role_name'?", true)) {

                            $permission = Permission::firstOrCreate(['name' => trim($permission_name)]);
                            $role->givePermissionTo(trim($permission_name));
                            $this->command->info('Permission added.');
                        }
                    }

                    // create users
                    if ($go_ahead || $this->command->confirm("Do you wish to create a USER with ROLE '$role_name'? [Y|n]", true)) {
                        if ($role_name == ROLE_ADMIN) {
                            $this->createAdmin();
                        } else {
                            $this->createUser($role_name);
                        }
                    }
                }
            }
        }

        $this->command->info('Default Roles and Permissions added.');

        if (!empty($this->created_users)) {
            $this->command->info('The following Users have been creted:');
            foreach ($this->created_users as $password => $user) {
                $this->command->warn('Full name: '.$user->name);
                $this->command->warn('Username: '.$user->email);
                $this->command->warn('Password: '.$password);
                $this->command->warn('Roles: ');
                $roles = $user->getRoleNames(); // Returns a collection
                foreach ($roles as $role) {
                    $this->command->warn('-  '.$role);
                }
                $this->command->info('-----------------------------');
            }
        }
    }

    /**
     * Create a user with given role
     *
     * @param $role
     */
    private function createAdmin()
    {
        // Must not already exist in the `email` column of `users` table
        $validator = Validator::make(['email' => \Config('auth.default_admin.username')], ['email' => 'unique:users']);

        if ($validator->fails()) {
            $this->command->warn('Default Admin user already created');
        }
        else {
            // Register the new user or whatever.
            $user = User::create([
                'name' => \Config('auth.default_admin.name'),
                'surname' => \Config('auth.default_admin.surname'),
                'email' => \Config('auth.default_admin.username'),
                'password' => \Config('auth.default_admin.password'),
                'is_storm' => true,
                ]);
            $user->assignRole(ROLE_ADMIN);
            $this->created_users[\Config('auth.default_admin.password')] = $user;
        }
    }

    /**
     * Create a user with given role
     *
     * @param $role
     */
    // TODO: non la sposto nelle Utils, la copio e basta: questa deve restare qua perché poi il Seeder andrà nel progetto base Net7Nautic
    private function createUser($role_name)
    {

        $faker = Faker::create();
        $email = Utils::getFakeStormEmail($role_name);

        // Register the new user or whatever.
        $password = $role_name;
        $user = User::create([
            'name' => $faker->name,
            'email' => $email,
            'password' => $password,
        ]);

        $user->assignRole($role_name);
        $this->created_users[$password] = $user;
    }
}
