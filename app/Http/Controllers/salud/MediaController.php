<?php

namespace App\Http\Controllers\salud;

use App\Http\Controllers\Controller;

class MediaController extends Controller
{
    public function showPublicacionMedia($filename)
    {
        return $this->servir('publicaciones', $filename);
    }

    public function showProfilePhoto($filename)
    {
        return $this->servir('profile-photos', $filename);
    }

    private function servir(string $carpeta, string $filename)
    {
        $base = realpath(storage_path('app/public/' . $carpeta));
        $path = $base ? realpath($base . DIRECTORY_SEPARATOR . basename($filename)) : false;

        if (!$path || !str_starts_with($path, $base . DIRECTORY_SEPARATOR) || !is_file($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => mime_content_type($path),
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
