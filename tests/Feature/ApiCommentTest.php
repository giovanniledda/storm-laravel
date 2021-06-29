<?php

namespace Tests\Feature;

use App\Boat;
use App\Comment;
use App\Permission;
use App\Project;
use App\Role;
use App\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestApiCase;

class ApiCommentTest extends TestApiCase
{
    /** create **/
    public function test_can_create_comment_related_to_task()
    {
        Role::firstOrCreate(['name' => ROLE_ADMIN]);
        Permission::firstOrCreate(['name' => PERMISSION_ADMIN]);
        $this->disableExceptionHandling();

        $proj_boat = $this->createBoatAndHisProject();
        $project = $proj_boat['project'];
        $task = $this->createProjectTask($project);

        /*** connessione con l'utente Admin (ma non è necessario sia ADMIN per commentare i task) */
        $admin1 = $this->_addUser(ROLE_ADMIN);
        $token_admin = $this->_grantTokenPassword($admin1);
        $this->assertIsString($token_admin);
        Passport::actingAs($admin1);

        $data = [
            'data' => [
                'attributes' => [
                    'task_id' => $task->id,
                    'author_id' => $admin1->id,
                    'body' => $this->faker->sentence(),
                ],
                'type' => 'comments',
            ],
        ];

        /*** creo un commento1 sul task */
        $response = $this->json('POST', route('api:v1:comments.create'), $data, $this->headers)
            ->assertJsonStructure(['data' => ['id']]);

        $content = json_decode($response->getContent(), true);
        $comment_id = $content['data']['id'];
        $comment = Comment::find($comment_id);
        $this->assertEquals($comment->id, $comment_id);
    }

    /** create **/
    public function test_can_create_multiple_comments_related_to_task()
    {
        Role::firstOrCreate(['name' => ROLE_ADMIN]);
        Permission::firstOrCreate(['name' => PERMISSION_ADMIN]);
        $this->disableExceptionHandling();

        $proj_boat = $this->createBoatAndHisProject();
        $project = $proj_boat['project'];
        $task1 = $this->createProjectTask($project);

        /*** connessione con l'utente Admin (ma non è necessario sia ADMIN per commentare i task) */
        $admin1 = $this->_addUser(ROLE_ADMIN);
        $token_admin = $this->_grantTokenPassword($admin1);
        $this->assertIsString($token_admin);
        Passport::actingAs($admin1);

        $data = [
            'data' => [
                'attributes' => [
                    'task_id' => $task1->id,
                    'author_id' => $admin1->id,
                    'body' => $this->faker->sentence(),
                ],
                'type' => 'comments',
            ],
        ];

        /*** creo N commenti sul task1 */
        $random_num1_of_comments = $this->faker->randomDigitNotNull;
        for ($i = 0; $i < $random_num1_of_comments; $i++) {
            $data['body'] = $this->faker->sentence();
            $response = $this->json('POST', route('api:v1:comments.create'), $data, $this->headers)
                ->assertJsonStructure(['data' => ['id']]);
        }

        /*** creo N commenti sul task2 */
        $task2 = $this->createProjectTask($project);

        $data = [
            'data' => [
                'attributes' => [
                    'task_id' => $task2->id,
                    'author_id' => $admin1->id,
                    'body' => $this->faker->sentence(),
                ],
                'type' => 'comments',
            ],
        ];
        $random_num2_of_comments = $this->faker->randomDigitNotNull;
        for ($i = 0; $i < $random_num2_of_comments; $i++) {
            $data['body'] = $this->faker->sentence();
            $response = $this->json('POST', route('api:v1:comments.create'), $data, $this->headers)
                ->assertJsonStructure(['data' => ['id']]);
        }

        /*** recupero SOLO i commenti del task1 */
        $data = ['filter[task_id]' => $task1->id];
        $response = $this->get(route('api:v1:comments.index', $data));

        $this->logResponse($response);

        $content = json_decode($response->getContent(), true);
        $comments = $content['data'];

        $this->assertCount($random_num1_of_comments, $comments);
    }
}
