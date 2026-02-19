<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dealer;
use App\Models\DealerGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DealerController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('q', ''));

        $query = Dealer::query()
            ->with('group')
            ->orderByDesc('created_at');

        if ($status && in_array($status, ['pending', 'active', 'passive', 'blocked'], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', '%' . $search . '%')
                    ->orWhere('contact_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('city', 'like', '%' . $search . '%')
                    ->orWhere('district', 'like', '%' . $search . '%');
            });
        }

        $dealers = $query->paginate(20)->appends([
            'status' => $status,
            'q' => $search,
        ]);

        return view('admin.dealers.index', [
            'dealers' => $dealers,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function edit(Dealer $dealer): View
    {
        $groups = DealerGroup::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.dealers.edit', [
            'dealer' => $dealer,
            'groups' => $groups,
        ]);
    }

    public function update(Request $request, Dealer $dealer): RedirectResponse
    {
        $cityDistrictMap = $this->trCityDistrictMap();

        $validated = $request->validate([
            'dealer_group_id' => ['nullable', 'exists:dealer_groups,id'],
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:dealers,email,' . $dealer->id],
            'phone' => ['required', 'string', 'max:20', function (string $attribute, mixed $value, \Closure $fail) {
                if (empty($value)) {
                    $fail('Telefon gerekli.');
                    return;
                }
                $digits = preg_replace('/\D+/', '', (string) $value);
                if (\Illuminate\Support\Str::startsWith($digits, '5')) {
                    $digits = '0' . $digits;
                }
                $digits = \Illuminate\Support\Str::substr($digits, 0, 11);

                if (! preg_match('/^05\d{9}$/', $digits)) {
                    $fail('Geçerli bir telefon girin (0555 555 55 55).');
                }
            }],
            'tax_office' => ['required', 'string', 'max:255'],
            'tax_number' => ['required', 'string', 'max:32', function (string $attribute, mixed $value, \Closure $fail) {
                if (empty($value)) {
                    $fail('Vergi no / TCKN gerekli.');
                    return;
                }
                $digits = preg_replace('/\D+/', '', (string) $value);
                if ($digits === '') {
                    return;
                }

                if (! (strlen($digits) === 10 || strlen($digits) === 11)) {
                    $fail('Vergi no / TCKN 10 veya 11 haneli olmalı.');
                }
            }],
            'city' => ['required', 'string', 'max:100', function (string $attribute, mixed $value, \Closure $fail) use ($cityDistrictMap) {
                if (empty($value)) {
                    $fail('İl gerekli.');
                    return;
                }
                $cityUpper = $this->trUpper((string) $value);
                if (! array_key_exists($cityUpper, $cityDistrictMap)) {
                    $fail('Lütfen listeden bir il seçin.');
                }
            }],
            'district' => ['required', 'string', 'max:100', function (string $attribute, mixed $value, \Closure $fail) use ($cityDistrictMap, $request) {
                if (empty($value)) {
                    $fail('İlçe gerekli.');
                    return;
                }
                $cityUpper = $this->trUpper((string) ($request->input('city') ?? ''));
                if (! array_key_exists($cityUpper, $cityDistrictMap)) {
                    $fail('Önce listeden bir il seçin.');
                    return;
                }

                $districtUpper = $this->trUpper((string) $value);
                if (! in_array($districtUpper, $cityDistrictMap[$cityUpper], true)) {
                    $fail('Lütfen seçtiğiniz ile ait bir ilçe girin.');
                }
            }],
            'address' => ['required', 'string', 'max:500'],
            'status' => ['required', 'string', 'in:pending,active,passive'],
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
            'status.in' => 'Geçersiz durum değeri.',
        ]);

        // Telefon ve vergi no'yu normalize et
        $phoneDigits = preg_replace('/\D+/', '', (string) $validated['phone']);
        if (\Illuminate\Support\Str::startsWith($phoneDigits, '5')) {
            $phoneDigits = '0' . $phoneDigits;
        }
        $validated['phone'] = \Illuminate\Support\Str::substr($phoneDigits, 0, 11);

        // Telefon unique kontrolü (mevcut bayi hariç)
        if (Dealer::where('phone', $validated['phone'])
            ->where('id', '!=', $dealer->id)
            ->exists()) {
            return redirect()
                ->route('admin.dealers.edit', $dealer)
                ->withErrors(['phone' => 'Bu telefon numarası zaten kayıtlı.'])
                ->withInput();
        }

        $validated['tax_number'] = preg_replace('/\D+/', '', (string) $validated['tax_number']);

        // İl/ilçe'yi uppercase yap
        if (!empty($validated['city'])) {
            $validated['city'] = $this->trUpper((string) $validated['city']);
        }
        if (!empty($validated['district'])) {
            $validated['district'] = $this->trUpper((string) $validated['district']);
        }

        $dealer->fill($validated);
        $dealer->save();

        return redirect()
            ->route('admin.dealers.edit', $dealer)
            ->with('success', 'Bayi bilgileri güncellendi.');
    }

    private function trUpper(string $value): string
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
    private function trCityDistrictMap(): array
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

    public function approve(Request $request, Dealer $dealer): RedirectResponse
    {
        // Edit ekranından gelen durum değişikliğinin onayı ezmemesi için
        // her zaman son halini alalım.
        $dealer->refresh();

        if (! $dealer->email_verified_at) {
            return back()->with('error', 'Bayi e-posta adresini doğrulamadan onaylanamaz.');
        }

        $dealer->status = 'active';
        $dealer->save();

        return back()->with('success', 'Bayi üyeliği onaylandı.');
    }

    public function reject(Request $request, Dealer $dealer): RedirectResponse
    {
        $dealer->status = 'passive';
        $dealer->save();

        return back()->with('success', 'Bayi üyeliği reddedildi/pasife alındı.');
    }
}

