@extends('layouts.admin')

@section('title', 'Ürünler')

@section('content')
    <livewire:admin.product-index />
@endsection

@push('scripts')
<script>
(function() {
    var shouldScrollTop = new URLSearchParams(window.location.search).get('scroll') === 'top';
    if (!shouldScrollTop) return;

    function doScroll() {
        if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
        window.scrollTo(0, 0);
    }
    function cleanUrl() {
        var params = new URLSearchParams(window.location.search);
        if (params.get('scroll') !== 'top') return;
        params.delete('scroll');
        var newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '') + window.location.hash;
        history.replaceState(null, '', newUrl);
    }

    document.addEventListener('DOMContentLoaded', function() {
        doScroll();
        cleanUrl();
        setTimeout(doScroll, 50);
        setTimeout(doScroll, 150);
    });
    window.addEventListener('load', doScroll);
})();
</script>
@endpush
