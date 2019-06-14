<?php

namespace Tests\Feature;

use App\User;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /**
     * @test
     * Test registration with Personal Access Token GRANT
     *
     * WARNING: To pass the test, a Client with personal token grant must exist (launch "artisan passport:install" if you didn't yet)
     */
    public function testSignupPersonalAccessClient()
    {
        //User's data
        $data = [
            'name' => 'Test',
            'email' => 'test@gmail.com',
            'password' => 'secret1234',
            'c_password' => 'secret1234',
        ];
        //Send post request
        $response = $this->json('POST', route('api.auth.signup'), $data);
        //Assert it was successful
        $response->assertStatus(200);
        //Assert we received a token
        $this->assertArrayHasKey('success', $response->json());
        $this->assertArrayHasKey('token', $response->json()['success']);

        //Delete data
        User::where('email', 'test@gmail.com')->delete();
    }

    /**
     * @test
     * Test login with Personal Access Token GRANT
     *
     * WARNING: To pass the test, a Client with personal token grant must exist (launch "artisan passport:install" if you didn't yet)
     */
    public function testLoginPersonalAccessClient()
    {
        //Create user
        User::create([
            'name' => 'test',
            'email' => 'test@gmail.com',
            'password' => bcrypt('secret1234')
        ]);
        //attempt login
        $response = $this->json('POST', route('api.auth.login'), [
            'email' => 'test@gmail.com',
            'password' => 'secret1234',
        ]);
        //Assert it was successful and a token was received
        $response->assertStatus(200);
        $this->assertArrayHasKey('success', $response->json());
        $this->assertArrayHasKey('token', $response->json()['success']);

        //Delete the user
        User::where('email', 'test@gmail.com')->delete();
    }


    /**
     * @test
     * Test Requesting Tokens with Password Token GRANT
     */
    public function testTokenRequestPasswordClient()
    {

        // Not existing User's data...trying just for fun :)
        $data = [
            'grant_type' => 'password',
            'client_id' => 'ID',
            'client_secret' => 'SECRET',
            'username' => 'unknown-user@fake-email.com',
            'password' => 'password',
            'scope' => '',
        ];
        // Send post request
        $response = $this->json('POST', route('passport.token'), $data);
        // Assert it was denied
        $response->assertStatus(401);

        // Create user
        $user = User::create([
            'name' => 'test',
            'email' => 'test@gmail.com',
            'password' => bcrypt('secret1234')
        ]);

        $clientRepository = new ClientRepository();
        $clientRepository->createPasswordGrantClient($user->id, \Config::get('auth.token_clients.password.name'), '/');

        $oauth_client_id = \Config::get('auth.token_clients.password.id');
        $oauth_client = $clientRepository->find($oauth_client_id);

        //User's data
        $data_ok = [
            'grant_type' => 'password',
            'client_id' => $oauth_client_id,
            'client_secret' => $oauth_client->secret,
            'username' => 'test@gmail.com',
            'password' => 'secret1234',
            'scope' => '',
        ];

        //Send post request

        $this->json('POST', route('passport.token'), $data_ok, ['Accept' => 'application/json']) // oauth/token
            ->assertStatus(200)
            ->assertJsonStructure(['token_type', 'expires_in', 'access_token', 'refresh_token']);

        //Delete data
        User::where('email', 'test@gmail.com')->delete();
    }

}
