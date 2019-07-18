<?php

use Illuminate\Database\Seeder;
use App\Permission;
use App\Role;
use App\User;
use App\Boat;
use Faker\Factory;

class DatabaseSeeder extends Seeder
{
    protected $faker;
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Factory::create();
        /** crea un utente per ogni ruolo */
        $roles_array = Role::defaultRoles();
        foreach ($roles_array as $role_key => $role_name) {
            if (trim($role_name)==='admin') {

            }
            $this->command->warn( $role_name);
            $email =  $role_name.'@storm.net';
            $validator = Validator::make(['email' => $email], ['email' => 'unique:users']);

        if ($validator->fails()) {
            $this->command->warn('Default '.$role_name.' user already created');
        }
        else {
            // Register the new user or whatever.
            $user = User::create([
                'name' => $role_name,
                'email' => $email,
                'password' => \Config('auth.default_admin.password'),
            ]);

            $this->computeRole($user);
            $this->command->info('Here is your '.$role_name.' details to login:');
            $this->command->warn($user->email);
            $this->command->warn('Password is "'.\Config('auth.default_admin.password').'"');
        }
        }

    }

    private function computeRole(User $user) {
        $role = Role::firstOrCreate(['name' => $user->name]);
        $user->assignRole($role);
        switch($user->name) {
            case 'admin':
                // assegno tutti i permessi
                $user->givePermissionTo('Admin');
            break;
            case 'backendmanager':

            break;
            case 'boatmanager':
                $boat1 = $this->createBoat();
                $this->boatAssociate($user, $boat1, $role='commander');
            break;
            case 'worker':
                $boat1 = $this->createBoat();
                $boat2 = $this->createBoat();
                $boat3 = $this->createBoat();
                $this->boatAssociate($user, $boat1, $role='commander');
                $this->boatAssociate($user, $boat2, $role='commander');
                $this->boatAssociate($user, $boat2, $role='commander');
            break;
        }
    }

    private function boatAssociate(User $user, Boat $boat, $role='commander') {
        $boat->associatedUsers()
             ->create(['role'=>$role, 'boat_id'=>$boat->id ,'user_id'=>$user->id])
             ->save();
     }

    private function createBoat():Boat {
        $boat_name = $this->faker->sentence;
        $boat = new Boat([
                'name' => $boat_name,
                'registration_number'=> $this->faker->randomDigitNotNull
            ]
        );
        $boat->save();
        return $boat;
    }



}
