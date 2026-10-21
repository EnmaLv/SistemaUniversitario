<?php

namespace App\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;

class EsquemaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Blueprint::macro('auditoria', function () {
            /** @var Blueprint $this */
            $this->foreignId('status_id')->default(1)->constrained('status', 'id_sta');
            $this->softDeletes();
            $this->unsignedBigInteger('created_id')->nullable();
            $this->unsignedBigInteger('updated_id')->nullable();
        });
    }
}
