<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Store\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            $store = $this->resolveCurrentStore($admin);
            return redirect($this->resolveLandingUrl($admin, $store));
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $rawInput = trim((string) $request->input('email'));
        $loginInput = strtolower($rawInput);

        $request->merge([
            'email' => $rawInput,
        ]);

        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ], [
            'email.required' => 'email, atau username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $isAddressNumber = is_numeric($rawInput);
        $admin = null;
        $store = null;

        if ($isAddressNumber) {
            // 1. Login via Address Number JDE (AN8)
            $store = Store::where('deleted', false)
                ->where('status', true)
                ->where('address_number', (int) $rawInput)
                ->first();

            if (!$store) {
                // Fallback jika input angka merupakan NIK / No. HP personil admin
                $admin = Admin::where('deleted', false)
                    ->where('is_active', true)
                    ->where(function ($query) use ($rawInput) {
                        $query->where('phone', $rawInput)
                              ->orWhereHas('user', function ($q) use ($rawInput) {
                                  $q->where('username', $rawInput);
                              });
                    })
                    ->first();

                if ($admin) {
                    $store = $this->resolveCurrentStore($admin);
                } else {
                    throw ValidationException::withMessages([
                        'email' => 'Nomor Pelanggan JDE (Address Number) tidak terdaftar atau toko sedang dinonaktifkan.',
                    ]);
                }
            } else {
                // Cari akun personil / owner toko terkait
                if (!empty($store->owner_user_id)) {
                    $admin = Admin::where('deleted', false)->where('is_active', true)->find($store->owner_user_id);
                }
                if (!$admin && !empty($store->email) && $store->email !== '-') {
                    $admin = Admin::where('deleted', false)->where('is_active', true)->whereRaw('LOWER(email) = ?', [strtolower(trim($store->email))])->first();
                }
                if (!$admin && !empty($store->code)) {
                    $admin = Admin::where('deleted', false)->where('is_active', true)->whereRaw('LOWER(email) LIKE ?', [strtolower($store->code) . '@%'])->first();
                }

                if (!$admin) {
                    throw ValidationException::withMessages([
                        'email' => "Toko {$store->name} (JDE: {$store->address_number}) belum memiliki akun login aktif. Silakan hubungi tim pusat.",
                    ]);
                }
            }
        } else {
            // 2. Login via Email atau Username
            $admin = Admin::where('deleted', false)
                ->where('is_active', true)
                ->where(function ($query) use ($loginInput) {
                    $query->whereRaw('LOWER(email) = ?', [$loginInput])
                          ->orWhereRaw('LOWER(name) = ?', [$loginInput])
                          ->orWhereRaw('LOWER(username) = ?', [$loginInput])
                          ->orWhereRaw('LOWER(sales_code) = ?', [$loginInput])
                          ->orWhereHas('user', function ($q) use ($loginInput) {
                              $q->whereRaw('LOWER(username) = ?', [$loginInput])
                                ->orWhereRaw('LOWER(email) = ?', [$loginInput]);
                          });
                })
                ->first();

            if (!$admin) {
                throw ValidationException::withMessages([
                    'email' => 'Email/username atau password salah.',
                ]);
            }

            // Ambil toko terkait akun admin ini (jika ada)
            $store = $this->resolveCurrentStore($admin);
        }

        // Pembatasan Akses: Akun Toko / Kasir (dibawah sales) dilarang login ke Web CMS, hanya diizinkan di Mobile POS
        $isStoreAccount = ($admin->user_type === 'store') 
            || in_array($admin->role_persona, ['store_owner', 'store_cashier', 'store'], true)
            || (method_exists($admin, 'isStore') && $admin->isStore());

        if ($isStoreAccount) {
            throw ValidationException::withMessages([
                'email' => 'Akun Toko/Kasir hanya dapat diakses melalui aplikasi Mobile POS. Akses login melalui Web CMS dibatasi untuk Sales Canvasser dan Manajemen Pusat.',
            ]);
        }

        $credentials = [
            'email' => $admin->email,
            'password' => $request->input('password'),
        ];

        if (Auth::guard('admin')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // Set context toko dan kanal ke session jika ada toko terkait
            if ($store) {
                $channel = $store->channels()->where('status', true)->where('deleted', false)->first();

                session()->put('current_store', [
                    'id'                  => $store->id,
                    'store_id'            => $store->id,
                    'code'                => $store->code,
                    'name'                => $store->name,
                    'address_number'      => (int) $store->address_number,
                    'tier_id'             => $store->tier_id,
                    'tier_name'           => $store->tier?->name ?? 'Standard',
                    'credit_limit'        => (float) $store->credit_limit,
                    'outstanding_balance' => (float) $store->outstanding_balance,
                    'available_credit'    => max(0, (float) $store->credit_limit - (float) $store->outstanding_balance),
                    'payment_term'        => (int) $store->payment_term,
                    'payment_term_days'   => (int) $store->payment_term,
                    'store_group_id'      => $store->store_group_id,
                    'email'               => $store->email,
                    'phone'               => $store->phone,
                    'address'             => $store->address,
                    'is_hq'               => ($store->code === 'ONLINE_RETAIL'),
                ]);

                if ($channel) {
                    session()->put('current_channel', [
                        'id'         => $channel->id,
                        'channel_id' => $channel->id,
                        'code'       => $channel->code,
                        'name'       => $channel->name,
                        'group_id'   => $channel->store_channel_group_id,
                    ]);
                }
            } else {
                session()->forget(['current_store', 'current_channel']);
            }

            $landingUrl = $this->resolveLandingUrl($admin, $store);
            return redirect()->intended($landingUrl);
        }

        throw ValidationException::withMessages([
            'email' => 'Email/username/No. JDE atau password salah.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->forget(['current_store', 'current_channel', 'assisted_by_sales']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    protected function resolveCurrentStore(Admin $admin): ?Store
    {
        // 1. Jika user adalah Super Admin atau Sales Canvasser, tidak dikunci ke toko apapun (tetap global)
        if ($admin->isSuperAdmin() || $admin->user_type === 'admin' || $admin->user_type === 'sales' || 
            (method_exists($admin, 'hasRole') && ($admin->hasRole('Admin') || $admin->hasRole('Sales Canvasser')))) {
            return null;
        }

        // 2. Direct link via store_id column di store_users
        if (!empty($admin->store_id)) {
            $store = Store::where('deleted', false)->where('status', true)->find($admin->store_id);
            if ($store) {
                return $store;
            }
        }

        // 3. Cari via relasi owner_user_id di tabel store
        $store = Store::where('deleted', false)->where('status', true)->where('owner_user_id', $admin->id)->first();
        if ($store) {
            return $store;
        }

        // 4. Fallback via email toko
        if (!empty($admin->email)) {
            $store = Store::where('deleted', false)->where('status', true)->whereRaw('LOWER(email) = ?', [strtolower(trim($admin->email))])->first();
        }

        return $store;
    }

    protected function resolveLandingUrl(Admin $admin, ?Store $store): string
    {
        // 1. Jika akun Toko (memiliki toko terkait) -> langsung ke Order & POS Toko
        if ($store) {
            return route('orders.index');
        }

        // 2. Jika role Warehouse Staff
        if (method_exists($admin, 'hasRole') && $admin->hasRole('Warehouse Staff')) {
            return route('inventory.index');
        }

        // 3. Super Admin / Admin Pusat (Full Akses) -> Dashboard Utama
        return route('dashboard');
    }
}
