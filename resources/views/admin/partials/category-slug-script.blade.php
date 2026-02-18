@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const nameEl = document.getElementById('name');
    const slugEl = document.getElementById('slug');
    if (!nameEl || !slugEl) return;

    const turkishMap = { 'ç':'c','Ç':'c','ğ':'g','Ğ':'g','ı':'i','İ':'i','ö':'o','Ö':'o','ş':'s','Ş':'s','ü':'u','Ü':'u' };
    const slugRegex = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;

    function slugify(text) {
        return String(text)
            .split('')
            .map(c => turkishMap[c] ?? c)
            .join('')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
    }

    function validateSlug() {
        const val = slugEl.value.trim();
        const valid = val === '' || slugRegex.test(val);
        slugEl.classList.toggle('input-error', !valid);
    }

    let slugManuallyEdited = false;

    slugEl.addEventListener('input', () => {
        slugManuallyEdited = true;
        validateSlug();
    });

    nameEl.addEventListener('input', () => {
        if (!slugManuallyEdited) {
            slugEl.value = slugify(nameEl.value);
        }
        validateSlug();
    });

    validateSlug();
});
</script>
@endpush
