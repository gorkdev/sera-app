function digitsOnly(s) {
    return String(s ?? "").replace(/\D/g, "");
}

function setCooldown(button, seconds) {
    if (!button) return;
    let remaining = Number(seconds ?? 0);
    if (!Number.isFinite(remaining) || remaining < 0) remaining = 0;

    const baseText = "Kodu tekrar gönder";
    const tick = () => {
        if (remaining <= 0) {
            button.disabled = false;
            button.textContent = baseText;
            return;
        }
        button.disabled = true;
        button.textContent = `${baseText} (${remaining}s)`;
        remaining -= 1;
        setTimeout(tick, 1000);
    };

    tick();
}

export function initDealerEmailVerify(selector = "[data-verify-form]") {
    const form = document.querySelector(selector);
    if (!form) return;

    const inputs = Array.from(form.querySelectorAll("[data-code-input]"));
    const hidden = form.querySelector("[data-verify-code]");

    const fillHidden = () => {
        const code = inputs.map((i) => digitsOnly(i.value).slice(0, 1)).join("");
        if (hidden) hidden.value = code;
        return code;
    };

    inputs.forEach((input, idx) => {
        input.addEventListener("input", () => {
            input.value = digitsOnly(input.value).slice(0, 1);
            fillHidden();
            if (input.value && inputs[idx + 1]) inputs[idx + 1].focus();
        });

        input.addEventListener("keydown", (e) => {
            if (e.key === "Backspace") {
                if (!input.value && inputs[idx - 1]) {
                    inputs[idx - 1].focus();
                    inputs[idx - 1].value = "";
                    e.preventDefault();
                    fillHidden();
                }
            }
            if (e.key === "ArrowLeft" && inputs[idx - 1]) {
                e.preventDefault();
                inputs[idx - 1].focus();
            }
            if (e.key === "ArrowRight" && inputs[idx + 1]) {
                e.preventDefault();
                inputs[idx + 1].focus();
            }
        });

        input.addEventListener("paste", (e) => {
            const pasted = digitsOnly(e.clipboardData?.getData("text") ?? "");
            if (!pasted) return;
            e.preventDefault();
            const chars = pasted.slice(0, inputs.length).split("");
            chars.forEach((ch, i) => {
                if (inputs[i]) inputs[i].value = ch;
            });
            fillHidden();
            const lastIdx = Math.min(chars.length, inputs.length) - 1;
            if (lastIdx >= 0 && inputs[lastIdx]) inputs[lastIdx].focus();
        });
    });

    form.addEventListener("submit", (e) => {
        const code = fillHidden();
        if (code.length !== inputs.length) {
            e.preventDefault();
            const firstEmpty = inputs.find((i) => !digitsOnly(i.value));
            firstEmpty?.focus?.();
        }
    });

    // Resend - AJAX + cooldown
    const resendForm = document.querySelector("[data-resend-form]");
    const resendBtn = document.querySelector("[data-resend-btn]");
    if (resendBtn) {
        const initialCooldown = Number(resendBtn.getAttribute("data-cooldown") ?? "0");
        if (initialCooldown > 0) setCooldown(resendBtn, initialCooldown);
    }

    if (resendForm && resendBtn) {
        resendForm.addEventListener("submit", async (e) => {
            e.preventDefault();

            const token =
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content") ?? "";

            resendBtn.disabled = true;
            resendBtn.classList.add("loading");

            try {
                const res = await fetch(resendForm.action, {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN": token,
                    },
                });
                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    // fallback: normal submit (server flash + redirect)
                    resendBtn.classList.remove("loading");
                    resendBtn.disabled = false;
                    resendForm.submit();
                    return;
                }

                const cooldown = Number(data?.cooldown_seconds ?? 60);
                resendBtn.classList.remove("loading");
                setCooldown(resendBtn, cooldown);
            } catch {
                resendBtn.classList.remove("loading");
                resendBtn.disabled = false;
                resendForm.submit();
            }
        });
    }
}

