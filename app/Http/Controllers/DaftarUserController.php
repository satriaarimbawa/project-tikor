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

    public function index(Request $request)
    {
        $usersRaw = $this->database->getReference('users')->getValue() ?? [];
        
        // Urutkan dari yang terbaru
        $usersRaw = array_reverse($usersRaw, true);
        
        $searchTerm = strtolower($request->input('search', ''));
        
        $dataFinal = [];
        foreach ($usersRaw as $uid => $user) {
            $username = $user['username'] ?? 'No Name';
            $email = $user['email'] ?? '-';
            $role = $user['role_user'] ?? 'No Role';

            // Filter Pencarian
            if ($searchTerm !== '') {
                $match = str_contains(strtolower($username), $searchTerm) || 
                         str_contains(strtolower($email), $searchTerm) ||
                         str_contains(strtolower($role), $searchTerm);
                
                if (!$match) continue;
            }

            $dataFinal[] = array_merge($user, ['id' => $uid]);
        }

        // Pagination Manual
        $perPage = (int) $request->input('perPage', 5);
        $currentPage = (int) $request->input('page', 1);
        $totalData = count($dataFinal);
        $totalPages = ceil($totalData / $perPage);
        $offset = ($currentPage - 1) * $perPage;
        
        $dataPaginated = array_slice($dataFinal, $offset, $perPage);

        return view('admin.register.daftar-user', [
            'users' => $dataPaginated,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'searchTerm' => $searchTerm,
            'perPage' => $perPage
        ]);
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

        // CEK DUPLIKASI DATA
        $usersRaw = $this->database->getReference('users')->getValue() ?? [];
        $newUsername = strtolower($request->username);
        $newEmail = strtolower($request->email);

        foreach ($usersRaw as $user) {
            if (strtolower($user['username'] ?? '') === $newUsername) {
                return back()->withErrors(['username' => 'Username ini sudah digunakan.'])->withInput();
            }
            if (strtolower($user['email'] ?? '') === $newEmail) {
                return back()->withErrors(['email' => 'Email ini sudah terdaftar.'])->withInput();
            }
        }

        FirebaseUser::create([
            'username' => $request->username,
            'email' => $request->email,
            'role_user' => $request->role_user,
            'password' => Hash::make($request->password),
            'is_online' => false
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

        // CEK DUPLIKASI DATA (Kecuali user itu sendiri)
        $usersRaw = $this->database->getReference('users')->getValue() ?? [];
        $newUsername = strtolower($request->username);
        $newEmail = strtolower($request->email);

        foreach ($usersRaw as $uid => $user) {
            if ($uid === $id) continue; // Lewati jika ID sama dengan yang sedang di-edit

            if (strtolower($user['username'] ?? '') === $newUsername) {
                return back()->withErrors(['username' => 'Username ini sudah digunakan oleh user lain.'])->withInput();
            }
            if (strtolower($user['email'] ?? '') === $newEmail) {
                return back()->withErrors(['email' => 'Email ini sudah terdaftar oleh user lain.'])->withInput();
            }
        }

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
