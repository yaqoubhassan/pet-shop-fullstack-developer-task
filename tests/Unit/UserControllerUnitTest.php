<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\JwtService;
use App\Http\Controllers\API\UserController;

class UserControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function testHandleFileUpload()
    {
        Storage::fake('public');

        // Create a fake file
        $file = UploadedFile::fake()->image('avatar.jpg');


        // Create an instance of the UserController
        $controller = new UserController(new JwtService());

        // Call the handleFileUpload method
        $fileUuid = $controller->testHandleFileUpload($file);

        // Assert that the file was stored
        Storage::disk('public')->assertExists('avatars/' . $file->hashName());

        // Assert that a record was created in the files table
        $this->assertDatabaseHas('files', [
            'uuid' => $fileUuid,
            'name' => 'avatar.jpg',
            'path' => 'avatars/' . $file->hashName(),
        ]);
    }
}
