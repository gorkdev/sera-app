/**
 * Bayi kayıt formu validasyonu
 */
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const digitsOnly = (v) => String(v ?? "").replace(/\D/g, "");

let trLocationsIndex = null;
let trLocationsIndexPromise = null;

const toTrUpper = (v) => String(v ?? "").trim().toLocaleUpperCase("tr-TR");

function titleCaseTr(value) {
    const trUpper = (s) => String(s).toLocaleUpperCase("tr-TR");
    const trLower = (s) => String(s).toLocaleLowerCase("tr-TR");
    const isAllLetters = (s) => /^[\p{L}]+$/u.test(s);
    const isLikelyAcronym = (s) =>
        isAllLetters(s) && s.length <= 4 && s === trUpper(s);

    const v = String(value ?? "");
    return v
        .split(/(\s+)/)
        .map((chunk) => {
            if (/^\s+$/.test(chunk)) return chunk;
            return chunk
                .split(/(-+)/)
                .map((part) => {
                    if (/^-+$/.test(part)) return part;
                    if (!isAllLetters(part)) return part;
                    if (isLikelyAcronym(part)) return part;
                    const lower = trLower(part);
                    return trUpper(lower.charAt(0)) + lower.slice(1);
                })
                .join("");
        })
        .join("");
}

async function ensureTrLocationsIndex() {
    if (trLocationsIndex) return trLocationsIndex;
    if (!trLocationsIndexPromise) {
        trLocationsIndexPromise = (async () => {
            const mod = await import("../data/tr-locations.json");
            const provinces = mod.default ?? mod;

            const byCityUpper = new Map();
            provinces.forEach((p) => {
                const cityUpper = String(p?.name ?? "").trim();
                if (!cityUpper) return;

                const cityPretty = titleCaseTr(
                    cityUpper.toLocaleLowerCase("tr-TR"),
                ).trim();

                const districtsUpper = (p?.districts ?? [])
                    .map((d) => String(d?.name ?? "").trim())
                    .filter(Boolean);
                const districtsUpperSet = new Set(districtsUpper);
                const districtsPretty = districtsUpper.map((d) =>
                    titleCaseTr(d.toLocaleLowerCase("tr-TR")).trim(),
                );

                byCityUpper.set(cityUpper, {
                    cityUpper,
                    cityPretty,
                    districtsUpperSet,
                    districtsPretty,
                });
            });

            trLocationsIndex = { byCityUpper };
            return trLocationsIndex;
        })();
    }

    return trLocationsIndexPromise;
}

function populateDatalist(datalistEl, values) {
    if (!datalistEl) return;
    datalistEl.innerHTML = "";
    values.forEach((v) => {
        const opt = document.createElement("option");
        opt.value = v;
        datalistEl.appendChild(opt);
    });
}

const rules = {
    company_name: (v) => (!v || !v.trim() ? "Şirket adı gerekli." : null),
    contact_name: (v) => (!v || !v.trim() ? "Yetkili adı gerekli." : null),
    email: (v) => {
        const val = (v ?? "").trim();
        if (!val) return "E-posta gerekli.";
        if (!emailRegex.test(val)) return "Geçerli bir e-posta adresi girin.";
        return null;
    },
    phone: (v) => {
        const d = digitsOnly(v);
        if (!d) return "Telefon gerekli.";
        // TR GSM: 05xx... toplam 11 hane (0 dahil)
        const normalized = d.startsWith("5") ? `0${d}` : d;
        if (normalized.length !== 11 || !normalized.startsWith("05")) {
            return "Geçerli bir telefon girin (0555 555 55 55).";
        }
        return null;
    },
    tax_office: (v) =>
        !v || !v.trim() ? "Vergi dairesi gerekli." : null,
    tax_number: (v) => {
        const d = digitsOnly(v);
        if (!d) return "Vergi numarası gerekli.";
        if (!(d.length === 10 || d.length === 11)) {
            return "Vergi no / TCKN 10 veya 11 haneli olmalı.";
        }
        return null;
    },
    city: (v) => {
        const val = String(v ?? "").trim();
        if (!val) return "İl gerekli.";
        if (trLocationsIndex?.byCityUpper) {
            const cityUpper = toTrUpper(val);
            if (!trLocationsIndex.byCityUpper.has(cityUpper)) {
                return "Lütfen listeden bir il seçin.";
            }
        }
        return null;
    },
    district: (form) => {
        const city = form.querySelector('input[name="city"]')?.value ?? "";
        const district =
            form.querySelector('input[name="district"]')?.value ?? "";
        const dVal = String(district ?? "").trim();
        if (!dVal) return "İlçe gerekli.";

        if (trLocationsIndex?.byCityUpper) {
            const cityUpper = toTrUpper(city);
            const info = trLocationsIndex.byCityUpper.get(cityUpper);
            if (!info) return "Önce listeden bir il seçin.";
            const districtUpper = toTrUpper(dVal);
            if (!info.districtsUpperSet.has(districtUpper)) {
                return "Lütfen seçtiğiniz ile ait bir ilçe girin.";
            }
        }

        return null;
    },
    address: (v) => (!v || !v.trim() ? "Adres gerekli." : null),
    kvkk_consent: (form) => {
        const el = form.querySelector('input[name="kvkk_consent"]');
        return el?.checked ? null : "KVKK aydınlatma metnini onaylamalısınız.";
    },
    password: (v) => {
        if (!v) return "Şifre gerekli.";
        if (v.length < 6) return "Şifre en az 6 karakter olmalı.";
        return null;
    },
    password_confirmation: (form) => {
        const pwd = form.querySelector('input[name="password"]')?.value ?? "";
        const conf =
            form.querySelector('input[name="password_confirmation"]')?.value ??
            "";
        return pwd !== conf ? "Şifreler eşleşmiyor." : null;
    },
};

function formatTrPhone(value) {
    // İstenen format: 0555 555 55 55
    let digits = String(value ?? "")
        .replace(/\D/g, "")
        .slice(0, 11);
    if (digits.startsWith("5")) digits = `0${digits}`.slice(0, 11);

    const p1 = digits.slice(0, 4);
    const p2 = digits.slice(4, 7);
    const p3 = digits.slice(7, 9);
    const p4 = digits.slice(9, 11);
    return [p1, p2, p3, p4].filter(Boolean).join(" ");
}

function togglePasswordVisibility(form) {
    form.querySelectorAll("[data-toggle-password]").forEach((btn) => {
        const targetId = btn.getAttribute("data-toggle-password");
        if (!targetId) return;
        const input = form.querySelector(`#${CSS.escape(targetId)}`);
        if (!input) return;

        const showIcon = btn.querySelector('[data-eye="show"]');
        const hideIcon = btn.querySelector('[data-eye="hide"]');

        btn.addEventListener("click", () => {
            const nextType = input.type === "password" ? "text" : "password";
            input.type = nextType;

            if (showIcon && hideIcon) {
                const isShowing = nextType === "text";
                showIcon.classList.toggle("hidden", isShowing);
                hideIcon.classList.toggle("hidden", !isShowing);
            }

            input.focus();
            try {
                const len = input.value?.length ?? 0;
                input.setSelectionRange(len, len);
            } catch {
                // bazı tarayıcılarda setSelectionRange desteklenmeyebilir
            }
        });
    });
}

function validateRegisterForm(form) {
    const errors = {};
    [
        "company_name",
        "contact_name",
        "email",
        "phone",
        "tax_office",
        "tax_number",
        "city",
        "address",
        "password",
    ].forEach((name) => {
        const field = form.querySelector(`[name="${name}"]`);
        const err = rules[name](field?.value ?? "");
        if (err) errors[name] = err;
    });

    const districtErr = rules.district(form);
    if (districtErr) errors.district = districtErr;

    const confErr = rules.password_confirmation(form);
    if (confErr) errors.password_confirmation = confErr;

    const kvkkErr = rules.kvkk_consent(form);
    if (kvkkErr) errors.kvkk_consent = kvkkErr;

    return errors;
}

function showFieldErrors(form, errors) {
    const fields = [
        "company_name",
        "contact_name",
        "email",
        "phone",
        "tax_office",
        "tax_number",
        "city",
        "district",
        "address",
        "password",
        "password_confirmation",
        "kvkk_consent",
    ];
    fields.forEach((name) => {
        const input = form.querySelector(`[name="${name}"]`);
        const errorEl = form.querySelector(`[data-error-for="${name}"]`);
        if (input) {
            if (errors[name]) {
                if (input.tagName === "TEXTAREA") {
                    input.classList.add("textarea-error");
                } else {
                    input.classList.add("input-error");
                }
                if (errorEl) {
                    errorEl.textContent = errors[name];
                    errorEl.classList.remove("hidden");
                }
            } else {
                input.classList.remove("input-error", "textarea-error");
                if (errorEl) {
                    errorEl.textContent = "";
                    errorEl.classList.add("hidden");
                }
            }
        }
    });
}

function setFieldError(form, name, message) {
    const input = form.querySelector(`[name="${name}"]`);
    const errorEl = form.querySelector(`[data-error-for="${name}"]`);
    if (!input || !errorEl) return;

    if (message) {
        if (input.type === "checkbox") {
            // checkbox'ta input-error sınıfı anlamsız; sadece mesaj göster
        } else if (input.tagName === "TEXTAREA") {
            input.classList.add("textarea-error");
        } else {
            input.classList.add("input-error");
        }
        errorEl.textContent = message;
        errorEl.classList.remove("hidden");
    } else {
        input.classList.remove("input-error", "textarea-error");
        errorEl.textContent = "";
        errorEl.classList.add("hidden");
    }
}

function clearFieldErrors(form) {
    form.querySelectorAll(".input-error, .textarea-error").forEach((el) => {
        el.classList.remove("input-error");
        el.classList.remove("textarea-error");
    });
    form.querySelectorAll("[data-error-for]").forEach((el) => {
        el.textContent = "";
        el.classList.add("hidden");
    });

    const success = form.parentElement?.querySelector("[data-register-success]");
    const error = form.parentElement?.querySelector("[data-register-error]");
    success?.classList.add("hidden");
    error?.classList.add("hidden");
}

export function initRegisterForm(formSelector = "[data-register-form]") {
    const form = document.querySelector(formSelector);
    if (!form) return;

    // Livewire re-render durumunda tekrar init edilecek; aynı DOM node için çift bind etme
    if (form.dataset.registerInit === "1") return;
    form.dataset.registerInit = "1";

    const attrNames = typeof form.getAttributeNames === "function" ? form.getAttributeNames() : [];
    const isLivewire = attrNames.some((n) => n.startsWith("wire:"));

    // İl/ilçe autocomplete: datalist + il'e bağlı ilçe önerileri
    void (async () => {
        try {
            const idx = await ensureTrLocationsIndex();
            const cityInput = form.querySelector('input[name="city"]');
            const districtInput = form.querySelector('input[name="district"]');
            const cityList = document.getElementById("tr_city_list");
            const districtList = document.getElementById("tr_district_list");

            if (cityInput && cityList) {
                const citiesPretty = Array.from(idx.byCityUpper.values()).map(
                    (v) => v.cityPretty,
                );
                populateDatalist(cityList, citiesPretty);
            }

            const updateDistricts = () => {
                if (!districtInput || !districtList) return;
                const cityUpper = toTrUpper(cityInput?.value ?? "");
                const info = idx.byCityUpper.get(cityUpper);
                if (!info) {
                    populateDatalist(districtList, []);
                    districtInput.placeholder = "Önce il seçin";
                    districtInput.disabled = true;
                    return;
                }
                populateDatalist(districtList, info.districtsPretty);
                districtInput.placeholder = "Örn: Kadıköy";
                districtInput.disabled = false;
            };

            cityInput?.addEventListener("input", () => {
                // İl değiştiyse ilçeyi temizle (seçili ilçe farklı ile ait olabilir)
                if (districtInput) districtInput.value = "";
                updateDistricts();
            });
            cityInput?.addEventListener("change", updateDistricts);
            cityInput?.addEventListener("blur", updateDistricts);

            updateDistricts();
        } catch {
            // dataset yüklenemezse form yine çalışır (sadece required validation kalır)
        }
    })();

    togglePasswordVisibility(form);

    ["company_name", "contact_name", "tax_office", "city", "district"].forEach((name) => {
        const input = form.querySelector(`input[name="${name}"]`);
        if (!input) return;

        const apply = () => {
            const start = input.selectionStart;
            const end = input.selectionEnd;
            const next = titleCaseTr(input.value);
            if (input.value !== next) input.value = next;
            // uzunluk değişmediği için caret genelde aynı kalır
            if (typeof start === "number" && typeof end === "number") {
                input.setSelectionRange(start, end);
            }
        };

        input.addEventListener("input", apply);
        input.addEventListener("blur", apply);
        apply();
    });

    // Telefon: 0555 555 55 55 formatında otomatik yaz
    const phoneInput = form.querySelector('input[name="phone"]');
    if (phoneInput) {
        const applyPhone = () => {
            const start = phoneInput.selectionStart ?? phoneInput.value.length;
            const prev = phoneInput.value ?? "";
            const next = formatTrPhone(prev);
            if (prev !== next) {
                phoneInput.value = next;
                // basit caret stratejisi: sona taşı (formatlama boşluk ekliyor)
                const caret = Math.min(
                    next.length,
                    start + (next.length - prev.length),
                );
                try {
                    phoneInput.setSelectionRange(caret, caret);
                } catch {
                    // noop
                }
            }
        };

        phoneInput.addEventListener("input", applyPhone);
        phoneInput.addEventListener("blur", applyPhone);
        applyPhone();
    }

    // E-posta: blur olduğunda formatı hemen kontrol et
    // Tüm alanlarda tutarlı davranış: blur olduğunda o alanı validate et
    const fieldNames = [
        "company_name",
        "contact_name",
        "email",
        "phone",
        "tax_office",
        "tax_number",
        "city",
        "district",
        "address",
        "password",
        "password_confirmation",
    ];

    const validateOne = (name) => {
        if (name === "password_confirmation") {
            return rules.password_confirmation(form);
        }
        if (name === "district") {
            return rules.district(form);
        }
        const el = form.querySelector(`[name="${name}"]`);
        return rules[name]?.(el?.value ?? "") ?? null;
    };

    fieldNames.forEach((name) => {
        const el = form.querySelector(`[name="${name}"]`);
        if (!el) return;
        el.addEventListener("blur", () => {
            const err = validateOne(name);
            setFieldError(form, name, err);
        });
    });

    const kvkkEl = form.querySelector('input[name="kvkk_consent"]');
    if (kvkkEl) {
        const run = () => setFieldError(form, "kvkk_consent", rules.kvkk_consent(form));
        kvkkEl.addEventListener("change", run);
        kvkkEl.addEventListener("blur", run);
    }

    // Vergi no / TCKN: sadece rakam yaz
    const taxNumberInput = form.querySelector('input[name="tax_number"]');
    if (taxNumberInput) {
        const apply = () => {
            const prev = taxNumberInput.value ?? "";
            const next = digitsOnly(prev).slice(0, 11);
            if (prev !== next) taxNumberInput.value = next;
        };
        taxNumberInput.addEventListener("input", apply);
        taxNumberInput.addEventListener("blur", apply);
        apply();
    }

    // Livewire formunda veya admin düzenleme formunda AJAX submit'e girmiyoruz (çakışmasın).
    if (isLivewire || form.dataset.registerMode === "edit") {
        return;
    }

    async function submitRegisterAjax() {
        const token =
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content") ?? "";

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn?.setAttribute("disabled", "disabled");
        submitBtn?.classList.add("loading");

        const registeredEmail =
            form.querySelector('input[name="email"]')?.value ?? "";

        const successBox = form.parentElement?.querySelector(
            "[data-register-success]",
        );
        const successText = form.parentElement?.querySelector(
            "[data-register-success-text]",
        );
        const errorBox = form.parentElement?.querySelector(
            "[data-register-error]",
        );
        const errorText = form.parentElement?.querySelector(
            "[data-register-error-text]",
        );

        try {
            const res = await fetch(form.action, {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": token,
                },
                body: new FormData(form),
            });

            const data = await res.json().catch(() => ({}));

            if (res.ok) {
                clearFieldErrors(form);

                if (successText) {
                    successText.textContent =
                        data?.message ??
                        "Kayıt başarılı. Hesabınız admin onayından sonra aktif olacaktır.";
                }
                successBox?.classList.remove("hidden");

                // Formu temizle (KVKK dahil)
                form.reset();

                // Doğrulama ekranına yönlendir
                if (data?.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 600);
                }
                return;
            }

            if (res.status === 422 && data?.errors) {
                const fieldErrors = {};
                Object.keys(data.errors).forEach((k) => {
                    fieldErrors[k] = Array.isArray(data.errors[k])
                        ? data.errors[k][0]
                        : String(data.errors[k]);
                });
                clearFieldErrors(form);
                showFieldErrors(form, fieldErrors);
                const firstErrorField = form.querySelector(".input-error");
                firstErrorField?.focus?.();
                return;
            }

            if (errorText) {
                errorText.textContent =
                    data?.message ??
                    "Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.";
            }
            errorBox?.classList.remove("hidden");
        } catch {
            const errorBox = form.parentElement?.querySelector(
                "[data-register-error]",
            );
            errorBox?.classList.remove("hidden");
        } finally {
            submitBtn?.removeAttribute("disabled");
            submitBtn?.classList.remove("loading");
        }
    }

    form.addEventListener("submit", (e) => {
        const errors = validateRegisterForm(form);
        clearFieldErrors(form);

        if (Object.keys(errors).length > 0) {
            e.preventDefault();
            showFieldErrors(form, errors);
            const firstErrorField = form.querySelector(".input-error");
            if (firstErrorField) firstErrorField.focus();
            return false;
        }

        e.preventDefault();
        submitRegisterAjax();
    });

    form.querySelectorAll("input, textarea").forEach((input) => {
        input.addEventListener("input", () => {
            const errors = validateRegisterForm(form);
            [
                "company_name",
                "contact_name",
                "email",
                "phone",
                "tax_office",
                "tax_number",
                "city",
                "district",
                "address",
                "password",
                "password_confirmation",
                "kvkk_consent",
            ].forEach((name) => {
                const inp = form.querySelector(`[name="${name}"]`);
                const errEl = form.querySelector(`[data-error-for="${name}"]`);
                if (
                    inp &&
                    errEl &&
                    !errEl.classList.contains("hidden") &&
                    !errors[name]
                ) {
                    inp.classList.remove("input-error", "textarea-error");
                    errEl.textContent = "";
                    errEl.classList.add("hidden");
                }
            });
        });
    });
}
