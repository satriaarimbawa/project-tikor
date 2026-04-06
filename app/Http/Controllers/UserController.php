<?php

namespace App\Http\Controllers;

use App\Models\FirebaseUser;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function index()
    {
        $reference = 'users';
        $users = $this->database->getReference($reference)->getValue();

        return view('admin.dashboardadmin', ['users' => $users]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.register.register');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

    $validatedData = $request->validate([
        'username' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'role_user' => 'required|string',
        'password' => 'required|string|min:6',
    ]);

    $nama = $request->input('username');
    $email = $request->input('email');
    $role_user = $request->input('role_user');
    $password = $request->input('password');

        $userId = FirebaseUser::create([
        'username' => $nama,
        'email' => $email,
        'role_user' => $role_user,
        'password' => $password,

    ]);

    return redirect('/register')->with('success', 'User berhasil dibuat!');
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}