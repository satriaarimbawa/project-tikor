<?php

namespace App\Http\Controllers; 

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Illuminate\Support\Facades\Hash;
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
            'password' => Hash::make($request->password),
        ]);

        return redirect('/daftar-user')->with('success', 'User berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $user = FirebaseUser::find($id);
        if (!$user) {
            return redirect('/daftar-user')->with('error', 'User tidak ditemukan!');
        }
        return view('admin.register.register', compact('user', 'id'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role_user' => 'required|string',
        ]);

        $data = [
            'username' => $request->username,
            'email' => $request->email,
            'role_user' => $request->role_user,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6']);
            $data['password'] = Hash::make($request->password);
        }

        FirebaseUser::update($id, $data);

        return redirect('/daftar-user')->with('success', 'User berhasil diperbarui!');
    }

    public function destroy($id)
    {
        FirebaseUser::delete($id);
        return redirect('/daftar-user')->with('success', 'User berhasil dihapus!');
    }
}
