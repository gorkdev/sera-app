<div
    x-data="{
        departureError: '',
        arrivedError: '',
        floristError: '',
        updateFloristFromArrived() {
            const arrEl = document.getElementById('party_arrived_at');
            const florEl = document.getElementById('party_florist_delivery_at');
            const v = arrEl?.value?.trim();
            if (!v || !florEl) return;
            const d = new Date(v);
            if (isNaN(d.getTime())) return;
            d.setDate(d.getDate() + 1);
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            const h = String(d.getHours()).padStart(2, '0');
            const min = String(d.getMinutes()).padStart(2, '0');
            florEl.value = y + '-' + m + '-' + day + 'T' + h + ':' + min;
            florEl.dispatchEvent(new Event('input', { bubbles: true }));
        },
        validate() {
            this.departureError = '';
            this.arrivedError = '';
            this.floristError = '';
            const dep = document.getElementById('party_departure_at')?.value;
            const arr = document.getElementById('party_arrived_at')?.value;
            const flor = document.getElementById('party_florist_delivery_at')?.value;
            const d = dep ? new Date(dep).getTime() : 0;
            const a = arr ? new Date(arr).getTime() : 0;
            const f = flor ? new Date(flor).getTime() : 0;
            if (d && a && d >= a) {
                this.departureError = 'Yola çıkış varıştan önce olmalıdır';
                this.arrivedError = 'Varış yola çıkıştan sonra olmalıdır';
            }
            if (a && f && f <= a) {
                this.floristError = 'Çiçekçilere teslimat varıştan sonra olmalıdır';
            }
        }
    }"
    x-init="
        $nextTick(() => {
            const runValidate = () => $data.validate();
            const dep = document.getElementById('party_departure_at');
            const arr = document.getElementById('party_arrived_at');
            const flor = document.getElementById('party_florist_delivery_at');
            [dep, flor].filter(Boolean).forEach(el => {
                el.addEventListener('input', runValidate);
                el.addEventListener('change', runValidate);
            });
            if (arr) {
                const onArrivedChange = () => {
                    $data.updateFloristFromArrived();
                    $data.validate();
                };
                arr.addEventListener('input', onArrivedChange);
                arr.addEventListener('change', onArrivedChange);
            }
        });
    "
    class="form-row form-row-2"
>
    <div class="form-control">
        <label for="emergency_contact" class="label"><span class="label-text font-medium">Acil İletişim (Sürücü Dışı)</span></label>
        <input type="text" id="emergency_contact" name="emergency_contact" value="{{ old('emergency_contact') }}"
            class="input input-bordered input-md w-full @error('emergency_contact') input-error @enderror"
            placeholder="Firma / lojistik acil numara veya e-posta" />
        <label class="label"><span class="label-text-alt">Sürücüye ulaşılamadığında aranacak</span></label>
        @error('emergency_contact')<p class="text-error text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="form-control">
        <label for="truck_status" class="label"><span class="label-text font-medium">Tır Durumu</span></label>
        <select id="truck_status" name="truck_status" wire:model.live="truck_status" class="select select-bordered select-md w-full">
            <option value="">— Seçin —</option>
            <option value="not_departed">Yola çıkmamış</option>
            <option value="on_road">Yolda</option>
            <option value="arrived">Seraya ulaşmış</option>
        </select>
        @error('truck_status')<p class="text-error text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="form-control">
        <label for="party_departure_at" class="label"><span class="label-text font-medium">Yola Çıkış Tarihi</span></label>
        <input type="datetime-local" id="party_departure_at" name="departure_at" wire:model.live="departure_at"
            class="input input-bordered input-md w-full @error('departure_at') input-error @enderror"
            :class="departureError && 'input-error'" />
        <p x-show="departureError" x-text="departureError" class="text-error text-sm mt-1"></p>
        @error('departure_at')<p class="text-error text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="form-control">
        <label for="party_arrived_at" class="label">
            <span class="label-text font-medium">Seraya Varış<span x-show="$wire.truck_status === 'not_departed' || $wire.truck_status === 'on_road'" x-cloak x-transition> (Tahmini)</span></span>
        </label>
        <input type="datetime-local" id="party_arrived_at" name="arrived_at" wire:model.live="arrived_at"
            @if($departure_at) min="{{ \Carbon\Carbon::parse($departure_at)->format('Y-m-d\TH:i') }}" @endif
            class="input input-bordered input-md w-full @error('arrived_at') input-error @enderror"
            :class="arrivedError && 'input-error'" />
        <p x-show="arrivedError" x-text="arrivedError" class="text-error text-sm mt-1"></p>
        @error('arrived_at')<p class="text-error text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="form-control">
        <label for="party_florist_delivery_at" class="label"><span class="label-text font-medium">Çiçekçilere Teslimat</span></label>
        <input type="datetime-local" id="party_florist_delivery_at" name="florist_delivery_at" wire:model.live="florist_delivery_at"
            @if($arrived_at) min="{{ \Carbon\Carbon::parse($arrived_at)->format('Y-m-d\TH:i') }}" @endif
            class="input input-bordered input-md w-full @error('florist_delivery_at') input-error @enderror"
            :class="floristError && 'input-error'" />
        <label class="label"><span class="label-text-alt">Varış +1 gün varsayılan; en erken varış tarihi seçilebilir.</span></label>
        <p x-show="floristError" x-text="floristError" class="text-error text-sm mt-1"></p>
        @error('florist_delivery_at')<p class="text-error text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="form-control">
        <label for="currency" class="label"><span class="label-text font-medium">Döviz</span></label>
        <select name="currency" id="currency" class="select select-bordered select-md w-full">
            <option value="EUR" {{ old('currency', 'EUR') == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
            <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD ($)</option>
            <option value="TRY" {{ old('currency') == 'TRY' ? 'selected' : '' }}>TRY (₺)</option>
        </select>
    </div>
</div>
