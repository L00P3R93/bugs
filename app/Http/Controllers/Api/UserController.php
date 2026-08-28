<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
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
        try {
            $validated = $request->validated();
            $password = $validated['password'];
            unset($validated['password']);
            $phone = $validated['phone'] == '' ? null : $validated['phone'];
            unset($validated['phone']);


            $user = User::forceCreate($validated);
            $user->newQueryWithoutScopes()->where('id', $user->id)
                ->update([
                    'password' => $password,
                    'phone' => $phone,
                ]);

            $user->update(['email_verified_at' => now()]);
            $user->assignRole('Tester');

            return response()->json([
                'message' => 'Bugs Account created successfully',
                'user_id' => $user->id,
            ], 201);

        } catch (\Exception $e) {
            Log::error("API Create Bugs account Error: {$e->getMessage()}");

            return response()->json([
                'message' => 'Failed to create Bugs Account',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
