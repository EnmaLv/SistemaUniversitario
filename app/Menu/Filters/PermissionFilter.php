<?php

namespace App\Menu\Filters;

use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;
use Illuminate\Support\Facades\Auth;

class PermissionFilter implements FilterInterface
{
    public function transform($item)
    {
        if (isset($item['submenu']) && is_array($item['submenu'])) {
            $newSub = [];
            foreach ($item['submenu'] as $sub) {
                $t = $this->transform($sub);
                if (empty($t['restricted'])) {
                    $newSub[] = $t;
                }
            }
            $item['submenu'] = $newSub;
            if (! count($item['submenu'])) {
                $item['restricted'] = true;
            }
            return $item;
        }

        $user = Auth::user();
        if (! $user) {
            $item['restricted'] = true;
            return $item;
        }

        foreach ($user->roles ?? [] as $r) {
            $nombreRol = mb_strtolower($r->nombre ?? '');
            $slugRol = mb_strtolower($r->slug ?? '');
            if (
                in_array($nombreRol, ['administrador', 'secretaria de bienestar'], true)
                || in_array($slugRol, ['administrador', 'secretaria-de-bienestar'], true)
            ) {
                return $item;
            }
        }

        $allowed = [];
        foreach ($user->roles ?? [] as $role) {
            $perms = $role->menu_permissions ?? [];
            if (is_array($perms)) {
                $allowed = array_merge($allowed, $perms);
            }
        }
        $allowed = array_values(array_unique($allowed));

        $extra = is_array($user->extra_permissions) ? $user->extra_permissions : (is_string($user->extra_permissions) ? json_decode($user->extra_permissions, true) : []);
        $userDeny = $extra['deny'] ?? [];
        $userAllow = $extra['allow'] ?? [];

        if (! empty($userDeny) && is_array($userDeny)) {
            $allowed = array_values(array_diff($allowed, $userDeny));
        }

        if (! empty($userAllow) && is_array($userAllow)) {
            $allowed = array_values(array_unique(array_merge($allowed, $userAllow)));
        }

        if (is_string($item) || isset($item['header'])) {
            $headerText = is_string($item) ? $item : $item['header'];
            $menu = config('menu_routes.php', []);
            $found = false;
            $visible = false;
            foreach ($menu as $m) {
                if (! $found) {
                    if ((is_string($m) && $m === $headerText) || (is_array($m) && isset($m['header']) && $m['header'] === $headerText)) {
                        $found = true;
                    }
                    continue;
                }

                if (is_string($m) || (is_array($m) && isset($m['header']))) {
                    break;
                }

                if ($this->isMenuItemVisible($m, $allowed)) {
                    $visible = true;
                    break;
                }
            }

            if (! $visible) {
                return is_string($item) ? ['header' => $headerText, 'restricted' => true] : array_merge($item, ['restricted' => true]);
            }

            return is_string($item) ? ['header' => $headerText] : $item;
        }

        $key = $item['key'] ?? ($item['url'] ?? ($item['route'] ?? ($item['text'] ?? null)));
        if (! $key) {
            $item['restricted'] = true;
            return $item;
        }

        if (! in_array($key, $allowed)) {
            $item['restricted'] = true;
        }

        return $item;
    }

    private function isMenuItemVisible($item, array $allowed)
    {
        if (! is_array($item)) return false;

        if (isset($item['submenu']) && is_array($item['submenu'])) {
            foreach ($item['submenu'] as $sub) {
                if ($this->isMenuItemVisible($sub, $allowed)) return true;
            }
            return false;
        }

        $key = $item['key'] ?? ($item['url'] ?? ($item['route'] ?? ($item['text'] ?? null)));
        if (! $key) return false;
        return in_array($key, $allowed);
    }
}
