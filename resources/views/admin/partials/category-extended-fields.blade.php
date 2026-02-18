@php
    $category = $category ?? null;
    $months = [
        '',
        'Ocak',
        'Şubat',
        'Mart',
        'Nisan',
        'Mayıs',
        'Haziran',
        'Temmuz',
        'Ağustos',
        'Eylül',
        'Ekim',
        'Kasım',
        'Aralık',
    ];
    $categoryAttributes = config('sera.category_attributes', []);
    $attrRequired = old('attribute_required', $category?->attribute_set['required'] ?? []);
    $attrVisible = old('attribute_visible', $category?->attribute_set['visible'] ?? []);
    $regionCitiesList = config('sera.region_cities', []);
    $regionRegionsList = config('sera.region_regions', []);
    $initialCities = old(
        'region_cities',
        $category && isset($category->region_restriction['cities'])
            ? implode(', ', $category->region_restriction['cities'])
            : '',
    );
    $initialRegions = old(
        'region_regions',
        $category && isset($category->region_restriction['regions'])
            ? implode(', ', $category->region_restriction['regions'])
            : '',
    );
@endphp

{{-- Sezon & Erişim --}}
<section class="admin-form-section">
    <h2 class="text-sm font-semibold uppercase tracking-wider text-base-content/70 mb-4 flex items-center gap-2">
        @svg('heroicon-o-calendar-days', 'h-4 w-4')
        Sezon & Erişim
    </h2>
    <div class="space-y-4">
        <div class="alert alert-info mb-4">
            @svg('heroicon-o-information-circle', 'h-5 w-5 shrink-0')
            <div>
                <p class="font-medium">Sezon</p>
                <p class="text-sm opacity-90">Kategori hangi aylarda katalogda görünsün? Örn: Nisan–Ekim seçerseniz
                    sadece bu aylarda listelenir.</p>
            </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="form-control">
                <label for="season_start_month" class="label">
                    <span class="label-text font-medium">Sezon Başlangıç</span>
                </label>
                <select name="season_start_month" id="season_start_month"
                    class="select select-md w-full @error('season_start_month') select-error @enderror">
                    <option value="0"
                        {{ (old('season_start_month', $category?->season_start_month) ?: 0) == 0 ? 'selected' : '' }}>
                        Tüm zamanlar</option>
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}"
                            {{ old('season_start_month', $category?->season_start_month) == $m ? 'selected' : '' }}>
                            {{ $months[$m] }}</option>
                    @endforeach
                </select>
                @error('season_start_month')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-control">
                <label for="season_end_month" class="label">
                    <span class="label-text font-medium">Sezon Bitiş</span>
                </label>
                <select name="season_end_month" id="season_end_month"
                    class="select select-md w-full @error('season_end_month') select-error @enderror">
                    <option value="0"
                        {{ (old('season_end_month', $category?->season_end_month) ?: 0) == 0 ? 'selected' : '' }}>Tüm
                        zamanlar</option>
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}"
                            {{ old('season_end_month', $category?->season_end_month) == $m ? 'selected' : '' }}>
                            {{ $months[$m] }}</option>
                    @endforeach
                </select>
                @error('season_end_month')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="form-control" id="inactive_outside_season_wrap">
            <label class="label cursor-pointer justify-start gap-3">
                <input type="checkbox" name="inactive_outside_season" value="1" id="inactive_outside_season"
                    class="checkbox checkbox-sm"
                    {{ old('inactive_outside_season', $category?->inactive_outside_season) ? 'checked' : '' }} />
                <div>
                    <span class="label-text font-medium">Sezon dışı pasif</span>
                    <p class="text-xs text-base-content/60 mt-0.5">Sezon dışındaki aylarda kategoriyi katalogda gösterme
                    </p>
                </div>
            </label>
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Görünür Olduğu Bayi Grupları</span>
            </label>
            <div class="flex flex-wrap gap-4 mt-2">
                @foreach ($dealerGroups as $group)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="visible_to_group_ids[]" value="{{ $group->id }}"
                            class="checkbox checkbox-sm @error('visible_to_group_ids') checkbox-error @enderror"
                            {{ in_array($group->id, old('visible_to_group_ids', $category?->visible_to_group_ids ?? [])) ? 'checked' : '' }} />
                        <span class="text-sm">{{ $group->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('visible_to_group_ids')
                <p class="text-error text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-control w-full sm:max-w-[12rem]">
            <label for="max_quantity_per_dealer_per_party" class="label">
                <span class="label-text font-medium">Parti Başına Kota</span>
            </label>
            <input type="number" id="max_quantity_per_dealer_per_party" name="max_quantity_per_dealer_per_party"
                value="{{ old('max_quantity_per_dealer_per_party', $category?->max_quantity_per_dealer_per_party) }}"
                class="input input-bordered input-md w-full @error('max_quantity_per_dealer_per_party') input-error @enderror"
                min="1" placeholder="Sınırsız" />
            @error('max_quantity_per_dealer_per_party')
                <p class="text-error text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>
</section>

{{-- Ürün Nitelikleri & Bölge Kısıtı --}}
<section class="admin-form-section">
    <h2 class="text-sm font-semibold uppercase tracking-wider text-base-content/70 mb-4 flex items-center gap-2">
        @svg('heroicon-o-cog-6-tooth', 'h-4 w-4')
        Ürün Nitelikleri & Bölge Kısıtı
    </h2>
    <div class="space-y-6">
        <div>
            <div class="alert alert-info mb-4">
                @svg('heroicon-o-information-circle', 'h-5 w-5 shrink-0')
                <div>
                    <p class="font-medium">Ürün nitelikleri</p>
                    <p class="text-sm opacity-90">Bu kategorideki ürün formunda hangi alanlar zorunlu veya görünür
                        olsun?</p>
                </div>
            </div>
            @if (count($categoryAttributes) > 0)
                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Zorunlu alanlar</span>
                        </label>
                        <div class="flex flex-wrap gap-3 mt-2">
                            @foreach ($categoryAttributes as $key => $label)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="attribute_required[]" value="{{ $key }}"
                                        class="checkbox checkbox-sm"
                                        {{ in_array($key, $attrRequired) ? 'checked' : '' }} />
                                    <span class="text-sm">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Görüntülenecek alanlar</span>
                        </label>
                        <div class="flex flex-wrap gap-3 mt-2">
                            @foreach ($categoryAttributes as $key => $label)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="attribute_visible[]" value="{{ $key }}"
                                        class="checkbox checkbox-sm"
                                        {{ in_array($key, $attrVisible) ? 'checked' : '' }} />
                                    <span class="text-sm">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <p class="text-sm text-base-content/60">Henüz nitelik tanımı yok. <code
                        class="bg-base-200 px-1 rounded">config/sera.php</code> içinde <code>category_attributes</code>
                    ekleyin.</p>
            @endif
        </div>

        <div>
            <div class="alert alert-info mb-4">
                @svg('heroicon-o-information-circle', 'h-5 w-5 shrink-0')
                <div>
                    <p class="font-medium">Bölge kısıtı</p>
                    <p class="text-sm opacity-90">Sadece belirli şehir veya bölgedeki bayilere açın. Örn: İzmir, Ege.
                    </p>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="form-control">
                    <label for="region_cities_input" class="label">
                        <span class="label-text font-medium">Sadece bu şehirlerdeki bayiler</span>
                    </label>
                    <div class="region-tag-input input input-bordered input-md w-full mt-2 min-h-10 flex flex-wrap items-center gap-2 py-2 pr-3 pl-3"
                        data-suggestions="{{ json_encode($regionCitiesList) }}" data-name="region_cities">
                        <div class="region-tags flex flex-wrap gap-2"></div>
                        <input type="text" id="region_cities_input"
                            class="flex-1 min-w-[8rem] bg-transparent border-none outline-none text-sm pl-0"
                            placeholder="Şehir yazın..." autocomplete="off" />
                        <input type="hidden" name="region_cities" id="region_cities"
                            value="{{ $initialCities }}" />
                    </div>
                </div>
                <div class="form-control">
                    <label for="region_regions_input" class="label">
                        <span class="label-text font-medium">Sadece bu bölgelerdeki bayiler</span>
                    </label>
                    <div class="region-tag-input input input-bordered input-md w-full mt-2 min-h-10 flex flex-wrap items-center gap-2 py-2 pr-3 pl-3"
                        data-suggestions="{{ json_encode($regionRegionsList) }}" data-name="region_regions">
                        <div class="region-tags flex flex-wrap gap-2"></div>
                        <input type="text" id="region_regions_input"
                            class="flex-1 min-w-[8rem] bg-transparent border-none outline-none text-sm pl-0"
                            placeholder="Bölge yazın..." autocomplete="off" />
                        <input type="hidden" name="region_regions" id="region_regions"
                            value="{{ $initialRegions }}" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const startEl = document.getElementById('season_start_month');
            const endEl = document.getElementById('season_end_month');
            if (startEl && endEl) {
                function updateEndState() {
                    const isAllTime = startEl.value === '0' || startEl.value === '';
                    endEl.disabled = isAllTime;
                    if (isAllTime) {
                        endEl.value = '0';
                    } else if (endEl.value === '0' || endEl.value === '') {
                        endEl.value = startEl.value;
                    }
                }
                startEl.addEventListener('change', updateEndState);
                updateEndState();
            }

            // Sezon dışı pasif: sadece sezon seçiliyse anlamlı
            const seasonWrap = document.getElementById('inactive_outside_season_wrap');
            const seasonStart = document.getElementById('season_start_month');
            if (seasonWrap && seasonStart) {
                function toggleSeasonWrap() {
                    const isAllTime = !seasonStart || seasonStart.value === '0' || seasonStart.value === '';
                    seasonWrap.style.opacity = isAllTime ? '0.5' : '1';
                    seasonWrap.style.pointerEvents = isAllTime ? 'none' : 'auto';
                    const cb = document.getElementById('inactive_outside_season');
                    if (cb) {
                        cb.disabled = isAllTime;
                        if (isAllTime) cb.checked = false;
                    }
                }
                seasonStart.addEventListener('change', toggleSeasonWrap);
                toggleSeasonWrap();
            }

            // Region tag inputs with autocomplete
            document.querySelectorAll('.region-tag-input').forEach(container => {
                const tagsEl = container.querySelector('.region-tags');
                const textInput = container.querySelector('input[type="text"]');
                const hiddenInput = container.querySelector('input[type="hidden"]');
                const suggestions = JSON.parse(container.dataset.suggestions || '[]');
                const name = container.dataset.name || '';

                let tags = [];
                let dropdown = null;

                function parseInitial() {
                    const val = (hiddenInput?.value || '').trim();
                    if (!val) return;
                    tags = val.split(/,\s*/).map(s => s.trim().toLocaleLowerCase('tr-TR')).filter(Boolean);
                }

                function syncHidden() {
                    if (hiddenInput) hiddenInput.value = tags.map(capitalizeTr).join(', ');
                }

                function capitalizeTr(s) {
                    if (!s) return '';
                    return s.charAt(0).toLocaleUpperCase('tr-TR') + s.slice(1).toLocaleLowerCase('tr-TR');
                }

                function renderTags() {
                    if (!tagsEl) return;
                    const xSvg =
                        '<svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>';
                    tagsEl.innerHTML = tags.map((t, i) =>
                        `<span class="badge badge-sm badge-ghost gap-1 pr-1 py-1.5">
                    ${escapeHtml(capitalizeTr(t))}
                    <button type="button" class="btn btn-ghost btn-xs p-0.5 min-h-0 h-5 w-5 rounded-full hover:bg-error/20 text-error" data-remove="${i}" aria-label="Kaldır">${xSvg}</button>
                </span>`
                    ).join('');
                    tagsEl.querySelectorAll('[data-remove]').forEach(btn => {
                        btn.addEventListener('click', () => {
                            tags.splice(parseInt(btn.dataset.remove), 1);
                            renderTags();
                            syncHidden();
                        });
                    });
                }

                function escapeHtml(s) {
                    const div = document.createElement('div');
                    div.textContent = s;
                    return div.innerHTML;
                }

                function showDropdown(filtered) {
                    hideDropdown();
                    if (filtered.length === 0) return;
                    dropdown = document.createElement('ul');
                    dropdown.className =
                        'absolute left-0 right-0 top-full mt-1 z-50 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-48 overflow-y-auto';
                    dropdown.style.minWidth = container.offsetWidth + 'px';
                    filtered.forEach(item => {
                        const li = document.createElement('li');
                        li.className = 'px-3 py-2 cursor-pointer hover:bg-base-200 text-sm';
                        li.textContent = capitalizeTr(item);
                        li.addEventListener('click', () => {
                            if (!tags.includes(item.toLowerCase())) {
                                tags.push(item.toLowerCase());
                                renderTags();
                                syncHidden();
                            }
                            textInput.value = '';
                            hideDropdown();
                            textInput.focus();
                        });
                        dropdown.appendChild(li);
                    });
                    container.style.position = 'relative';
                    container.appendChild(dropdown);
                }

                function hideDropdown() {
                    if (dropdown && dropdown.parentNode) dropdown.remove();
                    dropdown = null;
                }

                function filterSuggestions(q) {
                    const qn = (q || '').trim().toLowerCase();
                    if (!qn) return [];
                    const existing = new Set(tags);
                    return suggestions.filter(s =>
                        s.toLowerCase().includes(qn) && !existing.has(s.toLowerCase())
                    ).slice(0, 8);
                }

                textInput.addEventListener('input', () => {
                    const q = textInput.value;
                    const filtered = filterSuggestions(q);
                    showDropdown(filtered);
                });

                textInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const q = textInput.value.trim().toLowerCase();
                        if (dropdown && dropdown.children.length > 0) {
                            dropdown.children[0].click();
                        } else if (q && !tags.includes(q)) {
                            tags.push(q);
                            textInput.value = '';
                            renderTags();
                            syncHidden();
                        }
                    } else if (e.key === 'Backspace' && !textInput.value && tags.length > 0) {
                        tags.pop();
                        renderTags();
                        syncHidden();
                    }
                });

                textInput.addEventListener('blur', () => setTimeout(hideDropdown, 150));

                document.addEventListener('click', (e) => {
                    if (!container.contains(e.target)) hideDropdown();
                });

                parseInitial();
                renderTags();
            });
        });
    </script>
@endpush
