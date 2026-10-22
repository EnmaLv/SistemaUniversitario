<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        $conexion = $app['config']->get('database.default');
        $baseDatos = (string) $app['config']->get("database.connections.{$conexion}.database");

        if ($conexion !== 'sqlite' && ! str_ends_with($baseDatos, '_test')) {
            throw new RuntimeException("Las pruebas se negaron a ejecutarse sobre la base de datos '{$baseDatos}'. Usa una base que termine en _test.");
        }

        return $app;
    }
}
