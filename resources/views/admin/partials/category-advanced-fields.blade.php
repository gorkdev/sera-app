@php
    $category = $category ?? null;
    $requiredDocs = config('sera.category_required_documents', []);
    $categoryIcons = config('sera.category_icons', []);
    $featuredBadges = config('sera.category_featured_badges', []);
    $categoryColors = config('sera.category_colors', []);
    $reqDocsChecked = old('required_documents', $category?->required_documents ?? []);
    $badgesChecked = old('featured_badges', $category?->featured_badges ?? []);
@endphp

{{-- Gelişmiş Ayarlar (Accordion) --}}
<div class="collapse collapse-arrow bg-base-200/50 rounded-lg mt-4 pl-0">
    <input type="checkbox" name="advanced_toggle" />
    <div class="collapse-title font-medium flex items-center gap-2 min-h-0 py-4 pl-0 pr-4">
        @svg('heroicon-o-cpu-chip', 'h-5 w-5')
        Gelişmiş Ayarlar
    </div>
    <div class="collapse-content px-0">
        <div class="space-y-6 pt-2">
            {{-- Hasat ve Dikim --}}
            <div>
                <h3 class="text-xs font-semibold uppercase text-base-content/60 mb-3">Hasat ve Dikim</h3>
                <div class="form-control w-full sm:max-w-[12rem]">
                    <label for="default_growth_days" class="label">
                        <span class="label-text font-medium">Varsayılan yetişme süresi (gün)</span>
                    </label>
                    <input type="number" id="default_growth_days" name="default_growth_days"
                        value="{{ old('default_growth_days', $category?->default_growth_days) }}"
                        class="input input-bordered input-md w-full" min="1" max="365"
                        placeholder="90" />
                </div>
            </div>

            {{-- İdeal İklim --}}
            <div>
                <h3 class="text-xs font-semibold uppercase text-base-content/60 mb-3">İdeal iklim koşulları</h3>
                <p class="text-sm text-base-content/60 mb-2">Sensör bu aralığın dışına çıkarsa risk uyarısı verilir.</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="form-control">
                        <label for="ideal_temp_min" class="label">
                            <span class="label-text font-medium">Sıcaklık (°C) min–max</span>
                        </label>
                        <div class="flex gap-2">
                            <input type="number" id="ideal_temp_min" name="ideal_temp_min"
                                value="{{ old('ideal_temp_min', $category?->ideal_temp_min) }}"
                                class="input input-bordered input-md flex-1" step="0.1" placeholder="15" />
                            <input type="number" id="ideal_temp_max" name="ideal_temp_max"
                                value="{{ old('ideal_temp_max', $category?->ideal_temp_max) }}"
                                class="input input-bordered input-md flex-1" step="0.1" placeholder="28" />
                        </div>
                    </div>
                    <div class="form-control">
                        <label for="ideal_humidity_min" class="label">
                            <span class="label-text font-medium">Nem (%) min–max</span>
                        </label>
                        <div class="flex gap-2">
                            <input type="number" id="ideal_humidity_min" name="ideal_humidity_min"
                                value="{{ old('ideal_humidity_min', $category?->ideal_humidity_min) }}"
                                class="input input-bordered input-md flex-1" step="0.1" placeholder="40" />
                            <input type="number" id="ideal_humidity_max" name="ideal_humidity_max"
                                value="{{ old('ideal_humidity_max', $category?->ideal_humidity_max) }}"
                                class="input input-bordered input-md flex-1" step="0.1" placeholder="80" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Zorunlu Belgeler --}}
            @if(count($requiredDocs) > 0)
            <div>
                <h3 class="text-xs font-semibold uppercase text-base-content/60 mb-3">Zorunlu belgeler</h3>
                <div class="flex flex-wrap gap-3">
                    @foreach($requiredDocs as $key => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="required_documents[]" value="{{ $key }}" class="checkbox checkbox-sm"
                                {{ in_array($key, $reqDocsChecked) ? 'checked' : '' }} />
                            <span class="text-sm">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Fiyatlandırma --}}
            <div>
                <h3 class="text-xs font-semibold uppercase text-base-content/60 mb-3">Fiyatlandırma kuralları</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="form-control">
                        <label for="min_order_quantity" class="label">
                            <span class="label-text font-medium">Minimum sipariş (MoQ)</span>
                        </label>
                        <input type="number" id="min_order_quantity" name="min_order_quantity"
                            value="{{ old('min_order_quantity', $category?->min_order_quantity) }}"
                            class="input input-bordered input-md w-full" min="1" placeholder="500" />
                    </div>
                    <div class="form-control">
                        <label for="profit_margin_percent" class="label">
                            <span class="label-text font-medium">Varsayılan kâr marjı (%)</span>
                        </label>
                        <input type="number" id="profit_margin_percent" name="profit_margin_percent"
                            value="{{ old('profit_margin_percent', $category?->profit_margin_percent) }}"
                            class="input input-bordered input-md w-full" step="0.01" min="0" placeholder="25" />
                    </div>
                </div>
            </div>

            {{-- Görsel: Renk ve İkon --}}
            <div>
                <h3 class="text-xs font-semibold uppercase text-base-content/60 mb-3">Görsel kimlik</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Renk kodu</span>
                        </label>
                        <input type="hidden" name="color_code" id="color_code" value="{{ old('color_code', $category?->color_code ?? '') }}" />
                        <div class="flex flex-wrap gap-2" id="color_picker">
                            @foreach($categoryColors as $hex => $name)
                                <button type="button" class="color-option relative w-8 h-8 rounded-full transition-all duration-200 focus:outline-none flex items-center justify-center"
                                    data-color="{{ $hex }}"
                                    style="background-color: {{ $hex }}"
                                    title="{{ $name }}">
                                    <span class="color-check-icon opacity-0 transition-opacity">
                                        @svg('heroicon-o-check', 'h-5 w-5 text-white drop-shadow-sm')
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    @if(count($categoryIcons) > 0)
                    <div class="form-control">
                        <label for="icon" class="label">
                            <span class="label-text font-medium">İkon</span>
                        </label>
                        <select name="icon" id="icon" class="select select-md w-full">
                            <option value="">— Seçin —</option>
                            @foreach($categoryIcons as $key => $label)
                                <option value="{{ $key }}" {{ old('icon', $category?->icon) == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Öne çıkan rozetler --}}
            @if(count($featuredBadges) > 0)
            <div>
                <h3 class="text-xs font-semibold uppercase text-base-content/60 mb-3">Öne çıkan etiketler</h3>
                <div class="flex flex-wrap gap-3">
                    @foreach($featuredBadges as $key => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="featured_badges[]" value="{{ $key }}" class="checkbox checkbox-sm"
                                {{ in_array($key, $badgesChecked) ? 'checked' : '' }} />
                            <span class="text-sm">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const hidden = document.getElementById('color_code');
    const currentValue = (hidden?.value || '').trim();
    function updateSelection() {
        document.querySelectorAll('.color-option').forEach(btn => {
            const hex = btn.dataset.color;
            const icon = btn.querySelector('.color-check-icon');
            if (hidden?.value === hex) {
                btn.classList.add('scale-110');
                if (icon) icon.classList.remove('opacity-0');
            } else {
                btn.classList.remove('scale-110');
                if (icon) icon.classList.add('opacity-0');
            }
        });
    }
    updateSelection();
    document.querySelectorAll('.color-option').forEach(btn => {
        const hex = btn.dataset.color;
        btn.addEventListener('click', () => {
            const isSelected = hidden?.value === hex;
            hidden.value = isSelected ? '' : hex;
            updateSelection();
        });
    });
});
</script>
@endpush
