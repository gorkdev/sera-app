<?php

namespace App\Livewire\Dealer;

use App\Models\Dealer;
use App\Models\DealerGroup;
use App\Services\DealerEmailVerificationService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class RegisterForm extends Component
{
    public string $company_name = '';
    public string $contact_name = '';
    public string $email = '';
    public string $phone = '';
    public string $tax_office = '';
    public string $tax_number = '';
    public string $city = '';
    public string $district = '';
    public string $address = '';
    public bool $kvkk_consent = false;
    public string $password = '';
    public string $password_confirmation = '';

    public function render()
    {
        return view('livewire.dealer.register-form');
    }

    public function submit(DealerEmailVerificationService $verificationService)
    {
        $cityDistrictMap = self::trCityDistrictMap();

        $validated = $this->validate([
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
                if ($digits === '') {
                    $fail('Vergi no / TCKN gerekli.');

                    return;
                }

                if (! (strlen($digits) === 10 || strlen($digits) === 11)) {
                    $fail('Vergi no / TCKN 10 veya 11 haneli olmalı.');
                }
            }],
            'city' => ['required', 'string', 'max:100', function (string $attribute, mixed $value, \Closure $fail) use ($cityDistrictMap) {
                $cityUpper = self::trUpper((string) $value);
                if (! array_key_exists($cityUpper, $cityDistrictMap)) {
                    $fail('Lütfen listeden bir il seçin.');
                }
            }],
            'district' => ['required', 'string', 'max:100', function (string $attribute, mixed $value, \Closure $fail) use ($cityDistrictMap) {
                $cityUpper = self::trUpper((string) $this->city);
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
        ], [
            'company_name.required' => 'Şirket adı gerekli.',
            'contact_name.required' => 'Yetkili adı gerekli.',
            'email.required' => 'E-posta gerekli.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'email.unique' => 'Bu e-posta adresi zaten kayıtlı.',
            'phone.required' => 'Telefon gerekli.',
            'tax_office.required' => 'Vergi dairesi gerekli.',
            'tax_number.required' => 'Vergi no / TCKN gerekli.',
            'city.required' => 'İl gerekli.',
            'district.required' => 'İlçe gerekli.',
            'address.required' => 'Adres gerekli.',
            'kvkk_consent.accepted' => 'KVKK aydınlatma metnini onaylamalısınız.',
            'password.required' => 'Şifre gerekli.',
            'password.min' => 'Şifre en az 6 karakter olmalı.',
            'password.confirmed' => 'Şifreler eşleşmiyor.',
        ]);

        $defaultGroupId = DealerGroup::query()->where('is_default', true)->value('id');

        $phoneDigits = preg_replace('/\D+/', '', (string) $validated['phone']);
        if (Str::startsWith($phoneDigits, '5')) {
            $phoneDigits = '0'.$phoneDigits;
        }
        $phoneDigits = Str::substr($phoneDigits, 0, 11);

        // DB'de unique index varsa önce kontrol et (kullanıcıya düzgün hata dön)
        if ($phoneDigits && Dealer::withTrashed()->where('phone', $phoneDigits)->exists()) {
            $this->addError('phone', 'Bu telefon numarası zaten kayıtlı.');

            return;
        }

        $taxNumberDigits = preg_replace('/\D+/', '', (string) $validated['tax_number']);
        $cityUpper = self::trUpper((string) $validated['city']);
        $districtUpper = self::trUpper((string) $validated['district']);
        $taxType = strlen($taxNumberDigits) === 11 ? 'tckn' : 'tax';

        try {
            $dealer = Dealer::create([
                'dealer_group_id' => $defaultGroupId,
                'company_name' => $validated['company_name'],
                'contact_name' => $validated['contact_name'],
                'email' => $validated['email'],
                'phone' => $phoneDigits ?: null,
                'tax_office' => $validated['tax_office'],
                'tax_number' => $taxNumberDigits ?: $validated['tax_number'],
                'tax_type' => $taxType,
                'tax_type' => $validated['tax_type'],
                'city' => $cityUpper,
                'district' => $districtUpper,
                'address' => $validated['address'],
                'kvkk_consent' => true,
                'password' => Hash::make($validated['password']),
                'status' => 'pending',
            ]);
        } catch (QueryException $e) {
            $msg = (string) $e->getMessage();
            if (str_contains($msg, 'dealers_phone_unique')) {
                $this->addError('phone', 'Bu telefon numarası zaten kayıtlı.');

                return;
            }
            if (str_contains($msg, 'dealers_email_unique')) {
                $this->addError('email', 'Bu e-posta adresi zaten kayıtlı.');

                return;
            }

            throw $e;
        }

        session()->put('dealer_verification_id', $dealer->id);

        try {
            $verificationService->sendCode($dealer, (string) request()->ip());
        } catch (ValidationException $e) {
            // Throttling gibi durumlarda bile doğrulama ekranına götür
        }

        return redirect()
            ->route('dealer.verify.show')
            ->with('success', 'Doğrulama kodu e-postanıza gönderildi. Lütfen kodu girin.');
    }

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
}

