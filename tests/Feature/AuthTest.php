<?php

namespace Tests\Feature;

use App\User;
use function get_class_methods;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    private $_user_data = [
        'name' => 'Test',
        'email' => 'test@gmail.com',
        'password' => 'secret1234',
        'c_password' => 'secret1234',
    ];

    /**
     * Utility function: creates a Password Grant Token Client
     */
    private function _createTestPasswordGrantClient($user)
    {
        $clientRepository = new ClientRepository();
        $clientRepository->createPasswordGrantClient($user->id, \Config::get('auth.token_clients.password.name'), '/');

        $oauth_client_id = \Config::get('auth.token_clients.password.id');
        return $clientRepository->find($oauth_client_id);
    }

    /**
     * Utility function: creates a Test User
     */
    private function _createTestUser()
    {
        $data = $this->_user_data;
        unset($data['c_password']);

        //Create user
        return User::create($data);
    }

    /**
     * Utility function: delete the Test User
     */
    private function _deleteTestUser()
    {
        User::where('email', $this->_user_data['email'])->delete();
    }

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
    /*
    public function testSignupPersonalAccessClient()
    {
        // User's data
        $data = $this->_user_data;

        // Send post request
        $response = $this->json('POST', route('api.auth.signup'), $data)
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['attributes' => ['access_token']]]);

        // Delete data
        $this->_deleteTestUser();
    }
    */

    /**
     * @test
     * Test login with Personal Access Token GRANT
     *
     * WARNING: To pass the test, a Client with personal token grant must exist (launch "artisan passport:install" if you didn't yet)
     */
    /*
    public function testLoginPersonalAccessClient()
    {
        $u = $this->_createTestUser();

        //attempt login
        $response = $this->json('POST', route('api.auth.login'), [
            'email' => $this->_user_data['email'],
            'password' => $this->_user_data['password'],
        ])->assertStatus(200)
        ->assertJsonStructure(['data' => ['attributes' => ['access_token']]]);

        // Delete data
        $this->_deleteTestUser();
    }
    */
    
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
        $response = $this->json('POST', route('passport.token'), $data)
            ->assertStatus(401);

        $user = $this->_createTestUser();
        $oauth_client = $this->_createTestPasswordGrantClient($user);

        //User's data
        $data_ok = [
            'grant_type' => 'password',
            'client_id' => $oauth_client->id,
            'client_secret' => $oauth_client->secret,
            'username' => $this->_user_data['email'],
            'password' => $this->_user_data['password'],
            'scope' => '',
        ];

        //Send post request

        $this->json('POST', route('passport.token'), $data_ok) // oauth/token
            ->assertStatus(200)
            ->assertJsonStructure(['token_type', 'expires_in', 'access_token', 'refresh_token']);

        // Delete data
        $this->_deleteTestUser();
    }

    /**
     * @test
     * Test requesting Authenticated User with autenthicated request
     */
    public function testUserAuthenticatedRequest()
    {

        $user = $this->_createTestUser();
        $oauth_client = $this->_createTestPasswordGrantClient($user);

        //User's data
        $data_ok = [
            'grant_type' => 'password',
            'client_id' => $oauth_client->id,
            'client_secret' => $oauth_client->secret,
            'username' => $this->_user_data['email'],
            'password' => $this->_user_data['password'],
            'scope' => '',
        ];

        //Send post request

        $response = $this->json('POST', route('passport.token'), $data_ok) // oauth/token
            ->assertStatus(200)
            ->assertJsonStructure(['token_type', 'expires_in', 'access_token', 'refresh_token']);

        $token = $response->json()['access_token'];
        $response = $this->json('GET', route('api.auth.user'), [], ['Authorization' => 'Bearer '.$token])
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'type', 'attributes']]);

        // Delete data
        $this->_deleteTestUser();
    }

    /**
     * @test
     * Test requesting a refreshed Token previously acquired
     */
    public function testRefreshTokenRequest()
    {

        $user = $this->_createTestUser();
        $oauth_client = $this->_createTestPasswordGrantClient($user);

        // LOGGO l'UTENTE

        //User's data
        $data_ok = [
            'grant_type' => 'password',
            'client_id' => $oauth_client->id,
            'client_secret' => $oauth_client->secret,
            'username' => $this->_user_data['email'],
            'password' => $this->_user_data['password'],
            'scope' => '',
        ];

        // Send post request
        $response = $this->json('POST', route('passport.token'), $data_ok) // oauth/token
            ->assertStatus(200)
            ->assertJsonStructure(['token_type', 'expires_in', 'access_token', 'refresh_token']);

        $refresh_token = $response->json()['refresh_token'];

        // CHIEDO REFRESH TOKEN

        $refresh_token_data = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token,
            'client_id' => $oauth_client->id,
            'client_secret' => $oauth_client->secret,
            'scope' => '',
        ];

        $response = $this->json('POST', route('passport.token'), $refresh_token_data) // oauth/token
            ->assertStatus(200)
            ->assertJsonStructure(['expires_in', 'access_token', 'refresh_token']);

        // Delete data
        $this->_deleteTestUser();
    }

    /**
     * @test
     * Test reset some User's password
     */
    public function testResetPasswordRequest()
    {

        $user = $this->_createTestUser();

        // User's WRONG data
        $data_ko = [
            'email' => $this->_user_data['email'].'wrong',
        ];

        // Send post request
        $response = $this->json('POST', route('api.auth.password.reset'), $data_ko) // oauth/token
        ->assertStatus(500)
            ->assertJsonStructure(['errors']);

        // User's data
        $data_ok = [
            'email' => $this->_user_data['email'],
        ];

        // Send post request
        $response = $this->json('POST', route('api.auth.password.reset'), $data_ok) // oauth/token
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);

        // https://laracasts.com/discuss/channels/testing/testing-if-email-was-sent-with-out-sending-it?page=1#reply=402801
        $emails = $this->app->make('swift.transport')->driver()->messages();
//        dd(get_class_methods($emails[0]));

        $this->assertCount(1, $emails);
        $this->assertEquals([$this->_user_data['email']], array_keys($emails[0]->getTo()));
        $this->assertStringContainsString(config('app.name'), $emails[0]->getSubject());
        $this->assertStringContainsString('password/reset/', $emails[0]->getBody());

        // Delete data
        $this->_deleteTestUser();
    }

}
