@extends('layouts.admin')

@section('title', 'Stoklar')

@section('content')
    <livewire:admin.stock-index :partyId="$partyId ?? null" />
@endsection
