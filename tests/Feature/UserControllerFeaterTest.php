<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\JwtToken;
use App\Models\File;

class UserControllerFeaterTest extends TestCase
{
    /**
     * @test
     */
    public function testUserCreationWithAvatar()
    {
        Storage::fake('public');

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'address' => '123 Main St',
            'phone_number' => '1234567890',
            'is_marketing' => 0,
            'is_admin' => 0,
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ];

        $response = $this->postJson(route('user.create'), $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'uuid',
                    'first_name',
                    'last_name',
                    'email',
                    'avatar',
                    'address',
                    'phone_number',
                    'is_marketing',
                    'created_at',
                    'updated_at',
                    'token'
                ],
                'error',
                'errors',
                'extra'
            ]);

        $user = User::where('email', 'john.doe@example.com')->first();
        $this->assertNotNull($user);

        $fileRecord = File::where('uuid', $user->avatar)->first();
        $this->assertNotNull($fileRecord);
        Storage::disk('public')->assertExists($fileRecord->path);

        $token = JwtToken::where('user_id', $user->id)->first();
        $this->assertNotNull($token);
    }

    public function testUserCreationWithoutAvatar()
    {
        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'address' => '456 Elm St',
            'phone_number' => '0987654321',
            'is_marketing' => 0,
            'is_admin' => 0,
        ];

        $response = $this->postJson(route('user.create'), $data);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'uuid',
                         'first_name',
                         'last_name',
                         'email',
                         'avatar',
                         'address',
                         'phone_number',
                         'is_marketing',
                         'created_at',
                         'updated_at',
                         'token'
                     ],
                     'error',
                     'errors',
                     'extra'
                 ]);

        $user = User::where('email', 'jane.doe@example.com')->first();
        $this->assertNotNull($user);

        $token = JwtToken::where('user_id', $user->id)->first();
        $this->assertNotNull($token);
    }
}
