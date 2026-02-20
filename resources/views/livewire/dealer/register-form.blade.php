<div>
    <form wire:submit.prevent="submit" class="space-y-3" data-register-form>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
            <div class="flex flex-col gap-2">
                <label for="company_name" class="label py-0 font-medium">Şirket Adı</label>
                <input type="text" id="company_name" name="company_name" wire:model.defer="company_name"
                    class="input input-bordered w-full @error('company_name') input-error @enderror"
                    placeholder="Firma A.Ş." autocomplete="organization" />
                @error('company_name')
                    <p class="text-error text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="contact_name" class="label py-0 font-medium">Yetkili Adı</label>
                <input type="text" id="contact_name" name="contact_name" wire:model.defer="contact_name"
                    class="input input-bordered w-full @error('contact_name') input-error @enderror"
                    placeholder="Ad Soyad" autocomplete="name" />
                @error('contact_name')
                    <p class="text-error text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="register_email" class="label py-0 font-medium">E-posta</label>
                <input type="email" id="register_email" name="email" wire:model.defer="email"
                    class="input input-bordered w-full @error('email') input-error @enderror"
                    placeholder="ornek@firma.com" autocomplete="email" inputmode="email" />
                @error('email')
                    <p class="text-error text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="phone" class="label py-0 font-medium">Telefon</label>
                <input type="tel" id="phone" name="phone" wire:model.defer="phone"
                    class="input input-bordered w-full @error('phone') input-error @enderror"
                    placeholder="0555 555 55 55" autocomplete="tel" inputmode="tel" />
                @error('phone')
                    <p class="text-error text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="tax_office" class="label py-0 font-medium">Vergi Dairesi</label>
                <input type="text" id="tax_office" name="tax_office" wire:model.defer="tax_office"
                    class="input input-bordered w-full @error('tax_office') input-error @enderror"
                    placeholder="Örn: Kadıköy" autocomplete="off" />
                @error('tax_office')
                    <p class="text-error text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="tax_number" class="label py-0 font-medium">Vergi No</label>
                <input type="text" id="tax_number" name="tax_number" wire:model.defer="tax_number"
                    class="input input-bordered w-full @error('tax_number') input-error @enderror"
                    placeholder="1234567890" inputmode="numeric" autocomplete="off" maxlength="10" />
                @error('tax_number')
                    <p class="text-error text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="city" class="label py-0 font-medium">İl</label>
                <input type="text" id="city" name="city" wire:model.defer="city"
                    class="input input-bordered w-full @error('city') input-error @enderror" placeholder="Örn: İstanbul"
                    autocomplete="address-level1" list="tr_city_list" />
                @error('city')
                    <p class="text-error text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="district" class="label py-0 font-medium">İlçe</label>
                <input type="text" id="district" name="district" wire:model.defer="district"
                    class="input input-bordered w-full @error('district') input-error @enderror"
                    placeholder="Örn: Kadıköy" autocomplete="address-level2" list="tr_district_list" />
                @error('district')
                    <p class="text-error text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-2 sm:col-span-2">
                <label for="address" class="label py-0 font-medium">Adres</label>
                <textarea id="address" name="address" wire:model.defer="address" rows="3"
                    class="textarea textarea-bordered w-full @error('address') textarea-error @enderror" placeholder="Açık adres"
                    autocomplete="street-address"></textarea>
                @error('address')
                    <p class="text-error text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="register_password" class="label py-0 font-medium">Şifre</label>
                <div class="relative">
                    <input type="password" id="register_password" name="password" wire:model.defer="password"
                        class="input input-bordered w-full pr-12 @error('password') input-error @enderror"
                        placeholder="••••••••" autocomplete="new-password" />
                    <button type="button"
                        class="btn btn-ghost btn-sm btn-square absolute right-1 top-1/2 -translate-y-1/2"
                        data-toggle-password="register_password" aria-label="Şifreyi göster/gizle">
                        <span data-eye="show">@svg('heroicon-o-eye', 'h-5 w-5')</span>
                        <span data-eye="hide" class="hidden">@svg('heroicon-o-eye-slash', 'h-5 w-5')</span>
                    </button>
                </div>
                @error('password')
                    <p class="text-error text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="password_confirmation" class="label py-0 font-medium">Şifre Tekrar</label>
                <div class="relative">
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        wire:model.defer="password_confirmation" class="input input-bordered w-full pr-12"
                        placeholder="••••••••" autocomplete="new-password" />
                    <button type="button"
                        class="btn btn-ghost btn-sm btn-square absolute right-1 top-1/2 -translate-y-1/2"
                        data-toggle-password="password_confirmation" aria-label="Şifreyi göster/gizle">
                        <span data-eye="show">@svg('heroicon-o-eye', 'h-5 w-5')</span>
                        <span data-eye="hide" class="hidden">@svg('heroicon-o-eye-slash', 'h-5 w-5')</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-1">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="kvkk_consent" wire:model.defer="kvkk_consent"
                    class="checkbox checkbox-sm checkbox-primary rounded shrink-0" />
                <span class="text-sm text-base-content/80">
                    KVKK aydınlatma metnini okudum ve onaylıyorum.
                </span>
            </label>
            @error('kvkk_consent')
                <p class="text-error text-sm">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-full" wire:loading.attr="disabled" wire:target="submit">
            <span wire:loading.remove wire:target="submit">Kayıt Ol</span>
            <span wire:loading wire:target="submit" class="loading loading-spinner loading-sm"></span>
        </button>
    </form>

    {{-- İl/İlçe datasource --}}
    <datalist id="tr_city_list"></datalist>
    <datalist id="tr_district_list"></datalist>
</div>
