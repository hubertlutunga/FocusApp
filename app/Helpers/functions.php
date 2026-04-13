<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Session;

if (!function_exists('config')) {
    function config(?string $key = null, mixed $default = null): mixed
    {
        $config = $GLOBALS['config'] ?? [];

        if ($key === null) {
            return $config;
        }

        $segments = explode('.', $key);
        $value = $config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $basePath = dirname(__DIR__, 2);
        return $path ? $basePath . '/' . ltrim($path, '/') : $basePath;
    }
}

if (!function_exists('view_path')) {
    function view_path(string $view): string
    {
        return base_path('app/Views/' . str_replace('.', '/', $view) . '.php');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $baseUrl = rtrim((string) config('app.base_url', ''), '/');
        $path = '/' . ltrim($path, '/');
        $routePrefix = rtrim((string) config('app.route_prefix', ''), '/');

        if (str_starts_with($path, '/public/')) {
            return ($baseUrl === '' ? '' : $baseUrl) . $path;
        }

        $prefix = $routePrefix === '' ? '' : $routePrefix;

        if ($path === '/') {
            return ($baseUrl === '' ? '' : $baseUrl) . $prefix;
        }

        return ($baseUrl === '' ? '' : $baseUrl) . $prefix . $path;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $normalizedPath = 'public/' . ltrim($path, '/');
        $assetUrl = url($normalizedPath);
        $fullPath = base_path($normalizedPath);

        if (!is_file($fullPath)) {
            return $assetUrl;
        }

        $separator = str_contains($assetUrl, '?') ? '&' : '?';
        return $assetUrl . $separator . 'v=' . filemtime($fullPath);
    }
}

if (!function_exists('project_asset')) {
    function project_asset(string $path): string
    {
        $baseUrl = rtrim((string) config('app.base_url', ''), '/');
        return ($baseUrl === '' ? '' : $baseUrl) . '/' . ltrim($path, '/');
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): never
    {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('old')) {
    function old(string $key, string $default = ''): string
    {
        return (string) Session::get('old_input.' . $key, $default);
    }
}

if (!function_exists('old_value')) {
    function old_value(string $key, mixed $default = null): mixed
    {
        return Session::get('old_input.' . $key, $default);
    }
}

if (!function_exists('old_array')) {
    function old_array(string $key, array $default = []): array
    {
        $value = old_value($key, $default);
        return is_array($value) ? $value : $default;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        $key = (string) config('app.csrf_key', '_csrf_token');
        $token = Session::get($key);

        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set($key, $token);
        }

        return (string) $token;
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('verify_csrf')) {
    function verify_csrf(): void
    {
        $sessionToken = (string) Session::get((string) config('app.csrf_key', '_csrf_token'), '');
        $requestToken = $_POST['_token'] ?? '';

        if ($sessionToken === '' || !hash_equals($sessionToken, (string) $requestToken)) {
            Session::flash('alert', [
                'icon' => 'error',
                'title' => 'Session expirée',
                'text' => 'Veuillez réessayer votre action.',
            ]);
            redirect('/login');
        }
    }
}

if (!function_exists('flash')) {
    function flash(string $key, mixed $value = null): mixed
    {
        if (func_num_args() === 2) {
            Session::flash($key, $value);
            return null;
        }

        return Session::consumeFlash($key);
    }
}

if (!function_exists('current_path')) {
    function current_path(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $baseUrl = rtrim((string) config('app.base_url', ''), '/');
        $routePrefix = rtrim((string) config('app.route_prefix', ''), '/');

        if ($baseUrl !== '' && str_starts_with($uri, $baseUrl)) {
            $uri = substr($uri, strlen($baseUrl)) ?: '/';
        }

        if ($routePrefix !== '' && str_starts_with($uri, $routePrefix)) {
            $uri = substr($uri, strlen($routePrefix)) ?: '/';
        }

        $uri = '/' . trim($uri, '/');
        return $uri === '//' ? '/' : $uri;
    }
}

if (!function_exists('is_active_path')) {
    function is_active_path(array $paths): string
    {
        $currentPath = current_path();

        foreach ($paths as $path) {
            $normalized = '/' . trim($path, '/');
            if ($normalized === '//' || $normalized === '') {
                $normalized = '/';
            }

            if ($currentPath === $normalized) {
                return 'active';
            }
        }

        return '';
    }
}

if (!function_exists('status_badge_class')) {
    function status_badge_class(?string $status): string
    {
        return match (strtolower(trim((string) $status))) {
            'draft' => 'badge-status badge-status-draft',
            'validated', 'approved', 'received', 'paid', 'active' => 'badge-status badge-status-success',
            'partial_paid', 'sent', 'ordered' => 'badge-status badge-status-warning',
            'unpaid' => 'badge-status badge-status-danger',
            'cancelled', 'inactive' => 'badge-status badge-status-danger',
            'converted' => 'badge-status badge-status-primary',
            default => 'badge-status badge-status-default',
        };
    }
}

if (!function_exists('status_label')) {
    function status_label(?string $status): string
    {
        return match (strtolower(trim((string) $status))) {
            'draft' => 'Brouillon',
            'validated' => 'Validée',
            'partial_paid' => 'Partiellement payée',
            'unpaid' => 'Non réglée',
            'paid' => 'Payée',
            'cancelled' => 'Annulée',
            'sent' => 'Envoyé',
            'approved' => 'Approuvé',
            'converted' => 'Converti',
            'ordered' => 'Commandé',
            'received' => 'Reçu',
            'active' => 'Actif',
            'inactive' => 'Inactif',
            default => ucfirst(str_replace('_', ' ', trim((string) $status))),
        };
    }
}

if (!function_exists('category_type_badge_class')) {
    function category_type_badge_class(?string $type): string
    {
        return match (strtolower(trim((string) $type))) {
            'product' => 'badge-category badge-category-product',
            'service' => 'badge-category badge-category-service',
            'mixed' => 'badge-category badge-category-mixed',
            default => 'badge-category badge-category-default',
        };
    }
}

if (!function_exists('category_type_label')) {
    function category_type_label(?string $type): string
    {
        return match (strtolower(trim((string) $type))) {
            'product' => 'Produit',
            'service' => 'Service',
            'mixed' => 'Mixte',
            default => ucfirst(str_replace('_', ' ', trim((string) $type))),
        };
    }
}

if (!function_exists('payment_method_badge_class')) {
    function payment_method_badge_class(?string $method): string
    {
        return match (strtolower(trim((string) $method))) {
            'cash' => 'badge-payment badge-payment-cash',
            'mobile_money' => 'badge-payment badge-payment-mobile',
            'bank', 'bank_transfer' => 'badge-payment badge-payment-bank',
            'card' => 'badge-payment badge-payment-card',
            'cheque' => 'badge-payment badge-payment-cheque',
            'credit' => 'badge-payment badge-payment-warning',
            'other' => 'badge-payment badge-payment-other',
            default => 'badge-payment badge-payment-default',
        };
    }
}

if (!function_exists('payment_method_label')) {
    function payment_method_label(?string $method): string
    {
        return match (strtolower(trim((string) $method))) {
            'cash' => 'Espèces',
            'mobile_money' => 'Mobile Money',
            'bank' => 'Banque',
            'bank_transfer' => 'Virement',
            'card' => 'Carte',
            'cheque' => 'Chèque',
            'credit' => 'À crédit',
            'other' => 'Autre',
            default => ucfirst(str_replace('_', ' ', trim((string) $method))),
        };
    }
}

if (!function_exists('tax_rate_options')) {
    function tax_rate_options(): array
    {
        return [
            0.0 => 'Exonere',
            16.0 => 'TVA 16%',
        ];
    }
}

if (!function_exists('normalize_tax_rate')) {
    function normalize_tax_rate(mixed $value): float
    {
        $rate = round((float) $value, 2);

        foreach (array_keys(tax_rate_options()) as $allowedRate) {
            if (abs($rate - (float) $allowedRate) < 0.001) {
                return (float) $allowedRate;
            }
        }

        return 0.0;
    }
}

if (!function_exists('tax_rate_label')) {
    function tax_rate_label(mixed $rate): string
    {
        $normalizedRate = normalize_tax_rate($rate);

        if ($normalizedRate <= 0) {
            return 'Exonere';
        }

        return 'TVA (' . rtrim(rtrim(number_format($normalizedRate, 2, '.', ''), '0'), '.') . '%)';
    }
}

if (!function_exists('module_badge_class')) {
    function module_badge_class(?string $module): string
    {
        return match (strtolower(trim((string) $module))) {
            'clients' => 'badge-module badge-module-clients',
            'utilisateurs' => 'badge-module badge-module-auth',
            'fournisseurs' => 'badge-module badge-module-suppliers',
            'produits' => 'badge-module badge-module-products',
            'services' => 'badge-module badge-module-services',
            'factures' => 'badge-module badge-module-invoices',
            'devis' => 'badge-module badge-module-quotes',
            'paiements' => 'badge-module badge-module-payments',
            'depenses' => 'badge-module badge-module-expenses',
            'approvisionnements' => 'badge-module badge-module-procurements',
            'stock' => 'badge-module badge-module-stock',
            'categories' => 'badge-module badge-module-categories',
            'unites' => 'badge-module badge-module-units',
            'authentification' => 'badge-module badge-module-auth',
            'parametres_entreprise' => 'badge-module badge-module-settings',
            default => 'badge-module badge-module-default',
        };
    }
}

if (!function_exists('movement_label')) {
    function movement_label(?string $movementType): string
    {
        return match (strtolower(trim((string) $movementType))) {
            'entry' => 'Entrée manuelle',
            'exit' => 'Sortie manuelle',
            'adjustment' => 'Ajustement',
            'invoice_validation' => 'Sortie par facture',
            'invoice_cancellation' => 'Retour après annulation',
            'procurement_receipt' => 'Réception approvisionnement',
            'manual' => 'Mouvement manuel',
            default => ucfirst(str_replace('_', ' ', trim((string) $movementType))),
        };
    }
}

if (!function_exists('module_label')) {
    function module_label(?string $module): string
    {
        return match (strtolower(trim((string) $module))) {
            'utilisateurs' => 'Utilisateurs',
            'parametres_entreprise' => 'Paramètres entreprise',
            default => ucfirst(str_replace('_', ' ', trim((string) $module))),
        };
    }
}

if (!function_exists('unit_badge_class')) {
    function unit_badge_class(): string
    {
        return 'badge-unit';
    }
}

if (!function_exists('user_has_role')) {
    function user_has_role(array|string $roles): bool
    {
        return Auth::hasRole($roles);
    }
}

if (!function_exists('user_is_admin')) {
    function user_is_admin(): bool
    {
        return user_has_role('administrateur');
    }
}

if (!function_exists('user_can_access_caisse')) {
    function user_can_access_caisse(): bool
    {
        return user_has_role(['administrateur', 'caisse', 'caissier']);
    }
}

if (!function_exists('user_can_access_commercial')) {
    function user_can_access_commercial(): bool
    {
        return user_has_role(['administrateur', 'caisse', 'caissier', 'gestionnaire_stock']);
    }
}

if (!function_exists('user_can_access_stock_management')) {
    function user_can_access_stock_management(): bool
    {
        return user_has_role(['administrateur', 'gestionnaire_stock']);
    }
}

if (!function_exists('app_scope_description')) {
    function app_scope_description(): string
    {
        if (user_can_access_stock_management() && !user_can_access_caisse()) {
            return 'Suivi des produits, approvisionnements et mouvements de stock.';
        }

        if (user_can_access_caisse() && !user_can_access_stock_management()) {
            return 'Pilotage des ventes, devis, dépenses, règlements et rapports.';
        }

        return 'Pilotage des ventes, services, facturation et stock.';
    }
}
