<?php

namespace App\Http\Controllers; 

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use App\Models\FirebaseUser;

class DaftarUserController extends Controller
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function index()
    {
        // Ambil data user asli dari Firebase
        $users = $this->database->getReference('users')->getValue() ?? [];
        return view('admin.register.daftar-user', compact('users'));
    }

    public function create()
    {
        return view('admin.register.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role_user' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        FirebaseUser::create([
            'username' => $request->username,
            'email' => $request->email,
            'role_user' => $request->role_user,
            'password' => $request->password,
        ]);

        return redirect('/daftar-user')->with('success', 'User berhasil ditambahkan!');
    }
}