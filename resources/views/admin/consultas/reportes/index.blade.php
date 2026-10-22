@extends('layouts.app')

@section('content_header')
    <h1>Panel de Reportes</h1>
@stop

@section('content')
    <p>Bienvenido {{ auth()->user()->persona->nombres_per }}.</p>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
        console.log("Hi, I'm using the Laravel-AdminLTE package!");
    </script>
@stop

