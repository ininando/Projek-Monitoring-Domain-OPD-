<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Manajemen user — khusus Super Admin (middleware role:super_admin).
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            })
            // Super Admin lebih dulu. CASE dipakai karena urutan ENUM di MySQL mengikuti urutan deklarasi,
            // bukan abjad. Nilai yang di-bind adalah konstanta, bukan input user.
            ->orderByRaw('CASE WHEN role = ? THEN 0 ELSE 1 END', [User::ROLE_SUPER_ADMIN])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('user.index', compact('users', 'search'));
    }

    public function create(): View
    {
        return view('user.create', ['user' => new User(['role' => User::ROLE_ADMIN])]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        // Password di-hash otomatis oleh cast 'hashed' pada model.
        $user = User::create($request->validated());

        return redirect()->route('user.index')->with('success', "User {$user->name} berhasil ditambahkan.");
    }

    public function edit(User $user): View
    {
        return view('user.edit', compact('user'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        // Cegah Super Admin menurunkan role dirinya sendiri (bisa mengunci akses manajemen user).
        if ($user->is($request->user()) && $data['role'] !== $user->role) {
            return back()->withInput()->withErrors(['role' => 'Anda tidak dapat mengubah role akun Anda sendiri.']);
        }

        $user->update($data);

        return redirect()->route('user.index')->with('success', "User {$user->name} berhasil diperbarui.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return redirect()->route('user.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('user.index')->with('success', "User {$user->name} berhasil dihapus.");
    }
}
