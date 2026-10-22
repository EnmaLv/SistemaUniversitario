<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\BusGpsLog;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    BusGpsLog::withTrashed()->where('created_at', '<', now()->subDays(30))->forceDelete();
})->daily();

Schedule::command('deportes:marcar-vencidos')->hourly()->withoutOverlapping();
