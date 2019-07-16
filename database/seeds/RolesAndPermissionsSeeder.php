<?php

use App\Permission;
use App\Role;
use App\User;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Ask for db migration refresh, default is no
        if ($this->command->confirm('Do you wish to refresh migration before seeding, it will clear all old data ?')) {
            // Call the php artisan migrate:refresh
            $this->command->call('migrate:refresh');
            $this->command->warn("Data cleared, starting from blank database.");
        }

        // Seed the default permissions
        $permissions = Permission::defaultPermissions();

        foreach ($permissions as $key => $name) {
            if ($this->command->confirm("Do you wish to create permission $name?", true)) {
                Permission::firstOrCreate(['name' => $name]);
            }
        }

        $this->command->info('Default Permissions added.');

        // Confirm roles needed
        if ($this->command->confirm('Create Roles for users? [y|N]', true)) {

            // Ask for roles from input
//            $input_roles = $this->command->ask('Enter roles in comma separate format.', 'Admin,User');
//            $roles_array = explode(',', $input_roles);

            // Get roles from config file
            $roles_array = Role::defaultRoles();

            // add roles
            foreach ($roles_array as $role_key => $role_name) {

                if ($this->command->confirm("Do you wish to create role '$role_name'?", true)) {
                    $role = Role::firstOrCreate(['name' => trim($role_name)]);

                    if ($role->name == 'Admin') {
                        // assign all permissions
                        $role->syncPermissions(Permission::all());
                        $this->command->info('Admin granted all the permissions');

                        // create user for Admin only
                        $this->createAdmin($role);
                        $this->command->info("Role '$role' added successfully");
                    } else {
                        // for others by default only read access
                        $role->syncPermissions(Permission::where('name', 'LIKE', 'view_%')->get());
                    }
                }
            }

        } else {
            Role::firstOrCreate(['name' => 'User']);
            $this->command->info('Added only default user role.');
        }

    }

    /**
     * Create a user with given role
     *
     * @param $role
     */
    private function createAdmin($role)
    {

        // Must not already exist in the `email` column of `users` table
        $validator = Validator::make(['email' => \Config('auth.default_admin.username')], ['email' => 'unique:users']);

        if ($validator->fails()) {
            $this->command->warn('Default Admin user already created');
        }
        else {
            // Register the new user or whatever.
            $user = User::create([
                'name' => \Config('auth.default_admin.username'),
                'email' => \Config('auth.default_admin.username'),
                'password' => \Config('auth.default_admin.password'),
            ]);
            $user->assignRole($role->name);

            $this->command->info('Here is your admin details to login:');
            $this->command->warn($user->email);
            $this->command->warn('Password is "'.\Config('auth.default_admin.password').'"');
        }
    }
}
