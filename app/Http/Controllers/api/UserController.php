<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $per_page = $request->per_page ?? 10;
        $users    = User::whereNot('role', 'Admin')->paginate($per_page);
        return response()->json([
            'status'  => true,
            'message' => 'User fetched successfully',
            'data'    => $users,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'passcode' => 'required|string|max:6|min:6|unique:users,passcode',

        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json([
                'status'  => false,
                'message' => $errors,
            ], 422);
        }
        $user = User::create([
            'name'     => $request->name,
            'passcode' => $request->passcode,
            'role'     => 'User',
        ]);
        return response()->json([
            'status'  => true,
            'message' => 'User created successfully',
            'data'    => $user,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $user      = User::findOrFail($id);
            $validator = Validator::make($request->all(), [
                'name'     => 'required|string|max:255',
                'passcode' => 'nullable|string|max:6|min:6|unique:users,passcode,' . $user->id,
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->first();
                return response()->json([
                    'status'  => false,
                    'message' => $errors,
                ], 422);
            }
            $user->update([
                'name'     => $request->name,
                'passcode' => $request->passcode,
            ]);
            return response()->json([
                'status'  => true,
                'message' => 'User updated successfully',
                'data'    => $user,
            ]);
        } catch (Exception $e) {
            Log::info('User updated error' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'User not found',
                'data'    => null,
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = user::findOrFail($id);
            $user->delete();
            return response()->json([
                'status'  => true,
                'message' => 'User deleted successfully',
                'data'    => null,
            ]);
        } catch (Exception $e) {
            Log::info('User deleted error' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'User not found',
                'data'    => null,
            ]);
        }
    }
}
