<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;
use App\Models\Dealer;
use App\Models\DealerGroup;
use App\Services\DealerEmailVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private static function trUpper(string $value): string
    {
        $value = trim($value);
        $value = str_replace(['i', 'ı'], ['İ', 'I'], $value);

        if (function_exists('mb_strtoupper')) {
            return mb_strtoupper($value, 'UTF-8');
        }

        return strtoupper($value);
    }

    /**
     * @return array<string, array<int, string>> cityUpper => districtUpper[]
     */
    private static function trCityDistrictMap(): array
    {
        static $map = null;
        if (is_array($map)) {
            return $map;
        }

        $path = base_path('resources/data/tr-locations.json');
        $json = file_get_contents($path);
        $data = json_decode($json ?: '[]', true, 512, JSON_THROW_ON_ERROR);

        $map = [];
        foreach ($data as $province) {
            $cityUpper = (string) ($province['name'] ?? '');
            if ($cityUpper === '') {
                continue;
            }
            $districts = [];
            foreach (($province['districts'] ?? []) as $district) {
                $name = (string) ($district['name'] ?? '');
                if ($name !== '') {
                    $districts[] = $name;
                }
            }
            $map[$cityUpper] = $districts;
        }

        return $map;
    }

    public function showAuth()
    {
        return $this->showLoginForm();
    }

    public function showLoginForm()
    {
        if (Auth::guard('dealer')->check()) {
            return redirect()->route('panel');
        }

        return view('auth.dealer.login');
    }

    public function showRegisterForm()
    {
        if (Auth::guard('dealer')->check()) {
            return redirect()->route('panel');
        }

        return view('auth.dealer.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validateWithBag('login', [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::guard('dealer')->attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'credentials' => ['Girdiğiniz bilgiler hatalı.'],
            ])->errorBag('login');
        }

        $dealer = Auth::guard('dealer')->user();

        if (! $dealer->email_verified_at) {
            Auth::guard('dealer')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $request->session()->put('dealer_verification_id', $dealer->id);
            $request->session()->flash('needs_email_verification', true);
            $request->session()->flash('needs_email_verification_email', $this->maskEmail((string) $dealer->email));

            return redirect()->route('dealer.login');
        }

        if ($dealer->status !== 'active') {
            Auth::guard('dealer')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($dealer->status === 'pending') {
                // Kilit kural: admin onayı öncesi e-posta doğrulama zorunlu.
                // Buraya gelindiyse e-posta doğrulanmış durumda ama admin onayı yok.
                return redirect()->route('dealer.login')->with('admin_pending', true);
            }

            if ($dealer->status === 'passive') {
                return redirect()->route('dealer.login')->with('dealer_passive', true);
            }

            return redirect()->route('dealer.login')->with('dealer_blocked', true);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('panel'));
    }

    private function maskEmail(string $email): string
    {
        $email = trim($email);
        if (! str_contains($email, '@')) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);
        $local = trim($local);
        $domain = trim($domain);

        $localMasked = match (true) {
            strlen($local) <= 1 => '*',
            strlen($local) === 2 => substr($local, 0, 1).'*',
            default => substr($local, 0, 2).str_repeat('*', max(1, strlen($local) - 2)),
        };

        $domainParts = explode('.', $domain);
        $domainName = $domainParts[0] ?? $domain;
        $domainRest = implode('.', array_slice($domainParts, 1));
        $domainMasked = Str::substr($domainName, 0, 2).str_repeat('*', max(1, strlen($domainName) - 2));
        if ($domainRest) {
            $domainMasked .= '.'.$domainRest;
        }

        return $localMasked.'@'.$domainMasked;
    }

    public function logout(Request $request)
    {
        Auth::guard('dealer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('dealer.login');
    }

    public function register(Request $request, DealerEmailVerificationService $verificationService)
    {
        $cityDistrictMap = self::trCityDistrictMap();

        $validated = $request->validateWithBag('register', [
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:dealers,email'],
            'phone' => ['required', 'string', 'max:20', function (string $attribute, mixed $value, \Closure $fail) {
                $digits = preg_replace('/\D+/', '', (string) $value);
                if (Str::startsWith($digits, '5')) {
                    $digits = '0'.$digits;
                }
                $digits = Str::substr($digits, 0, 11);

                if (! preg_match('/^05\d{9}$/', $digits)) {
                    $fail('Geçerli bir telefon girin (0555 555 55 55).');
                }
            }],
            'tax_office' => ['required', 'string', 'max:255'],
            'tax_number' => ['required', 'string', 'max:32', function (string $attribute, mixed $value, \Closure $fail) {
                $digits = preg_replace('/\D+/', '', (string) $value);
                if (! ($digits !== '' && (strlen($digits) === 10 || strlen($digits) === 11))) {
                    $fail('Vergi no / TCKN 10 veya 11 haneli olmalı.');
                }
            }],
            'city' => ['required', 'string', 'max:100', function (string $attribute, mixed $value, \Closure $fail) use ($cityDistrictMap) {
                $cityUpper = self::trUpper((string) $value);
                if (! array_key_exists($cityUpper, $cityDistrictMap)) {
                    $fail('Lütfen listeden bir il seçin.');
                }
            }],
            'district' => ['required', 'string', 'max:100', function (string $attribute, mixed $value, \Closure $fail) use ($request, $cityDistrictMap) {
                $cityUpper = self::trUpper((string) $request->input('city', ''));
                if (! array_key_exists($cityUpper, $cityDistrictMap)) {
                    $fail('Önce listeden bir il seçin.');

                    return;
                }

                $districtUpper = self::trUpper((string) $value);
                if (! in_array($districtUpper, $cityDistrictMap[$cityUpper], true)) {
                    $fail('Lütfen seçtiğiniz ile ait bir ilçe girin.');
                }
            }],
            'address' => ['required', 'string', 'max:500'],
            'kvkk_consent' => ['accepted'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:6'],
        ], [
            'company_name.required' => 'Şirket adı gerekli.',
            'contact_name.required' => 'Yetkili adı gerekli.',
            'email.required' => 'E-posta gerekli.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'email.unique' => 'Bu e-posta adresi zaten kayıtlı.',
            'phone.required' => 'Telefon gerekli.',
            'password.required' => 'Şifre gerekli.',
            'password.min' => 'Şifre en az 6 karakter olmalı.',
            'password.confirmed' => 'Şifreler eşleşmiyor.',
            'password_confirmation.required' => 'Şifre tekrar gerekli.',
            'password_confirmation.min' => 'Şifre tekrar en az 6 karakter olmalı.',
            'tax_office.required' => 'Vergi dairesi gerekli.',
            'tax_number.required' => 'Vergi numarası gerekli.',
            'city.required' => 'İl gerekli.',
            'district.required' => 'İlçe gerekli.',
            'address.required' => 'Adres gerekli.',
            'kvkk_consent.accepted' => 'KVKK aydınlatma metnini onaylamalısınız.',
        ]);

        $defaultGroupId = DealerGroup::query()->where('is_default', true)->value('id');

        $phoneDigits = preg_replace('/\D+/', '', (string) $validated['phone']);
        if (Str::startsWith($phoneDigits, '5')) {
            $phoneDigits = '0'.$phoneDigits;
        }
        $phoneDigits = Str::substr($phoneDigits, 0, 11);

        $taxNumberDigits = preg_replace('/\D+/', '', (string) $validated['tax_number']);

        $cityUpper = self::trUpper((string) $validated['city']);
        $districtUpper = self::trUpper((string) $validated['district']);

        $taxType = strlen($taxNumberDigits) === 11 ? 'tckn' : 'tax';

        $dealer = Dealer::create([
            'dealer_group_id' => $defaultGroupId,
            'company_name' => $validated['company_name'],
            'contact_name' => $validated['contact_name'],
            'email' => $validated['email'],
            'phone' => $phoneDigits ?: null,
            'tax_office' => $validated['tax_office'],
            'tax_number' => $taxNumberDigits ?: $validated['tax_number'],
            'tax_type' => $taxType,
            'city' => $cityUpper,
            'district' => $districtUpper,
            'address' => $validated['address'],
            'kvkk_consent' => true,
            'password' => Hash::make($validated['password']),
            'status' => 'pending',
        ]);

        $request->session()->put('dealer_verification_id', $dealer->id);
        $verificationService->sendCode($dealer, (string) $request->ip());

        $message = 'Doğrulama kodu e-postanıza gönderildi. Lütfen kodu girin.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'dealer_id' => $dealer->id,
                'redirect' => route('dealer.verify.show'),
            ], 201);
        }

        return redirect()->route('dealer.verify.show')->with('success', $message);
    }
}
