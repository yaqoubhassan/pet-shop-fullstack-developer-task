<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Services\JwtService;
use App\Models\User;
use App\Models\File;
use App\Http\Resources\UserResource;
use App\Http\Requests\StoreUserRequest;
use App\Http\Controllers\Controller;
use App\Models\JwtToken;

class UserController extends Controller
{
    protected $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        // Begin a transaction
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Handle file upload
            if ($request->hasFile('avatar')) {
                $data['avatar'] = $this->handleFileUpload($request->file('avatar'));
            }

            $data['uuid'] = (string) Str::uuid();
            $data['password'] = Hash::make($data['password']);

            $user = User::create($data);

            // Generate JWT token
            $token = $this->jwtService->generateToken($user);

            $this->saveToken($user);

            // Commit the transaction
            DB::commit();

            // Prepare the response data
            $response = [
                'success' => 1,
                'data' => new UserResource($user->fresh(), $token),
                'error' => null,
                'errors' => [],
                'extra' => []
            ];

            return response()->json($response, 201);
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Wrapper method to test handleFileUpload.
     */
    public function testHandleFileUpload($file)
    {
        return $this->handleFileUpload($file);
    }

    private function handleFileUpload($file)
    {
        $filePath = $file->store('avatars', 'public');
        $fileUuid = (string) Str::uuid();

        $fileRecord = File::create([
            'uuid' => $fileUuid,
            'name' => $file->getClientOriginalName(),
            'path' => $filePath,
            'size' => $file->getSize(),
            'type' => $file->getMimeType(),
        ]);

        return $fileRecord->uuid;
    }

    private function saveToken($user)
    {
        JwtToken::create([
            'user_id' => $user->id,
            'unique_id' => (string) Str::uuid(),
            'token_title' => 'User Auth Token',
            'permissions' => json_encode(['*']),
            'restrictions' => null,
            'expires_at' => now()->addHours(1),
            'last_used_at' => now(),
            'refreshed_at' => now(),
        ]);
    }
}
