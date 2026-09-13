@extends('layouts.app')

@push('styles')
    @stack('css')
    @yield('css')
@endpush

@push('scripts')
    @stack('js')
    @yield('js')
@endpush
