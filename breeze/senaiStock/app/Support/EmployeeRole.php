<?php

namespace App\Support;

use Illuminate\Http\Request;

class EmployeeRole
{
    public const COORDENADOR = 'Coordenador';

    public const PROFESSOR = 'Professor';

    public static function fromSession(Request $request): ?string
    {
        return $request->session()->get('employee.cargo');
    }

    public static function defaultView(?string $role): string
    {
        return match ($role) {
            self::PROFESSOR => 'teacher_requests',
            default => 'insights',
        };
    }

    /**
     * @return list<string>
     */
    public static function allowedViews(?string $role): array
    {
        return match ($role) {
            self::PROFESSOR => ['teacher_requests'],
            self::COORDENADOR => [
                'insights',
                'teacher_requests',
                'library',
                'book_registration',
                'stock',
                'reports',
                'purchases',
                'classes',
                'courses',
                'people',
            ],
            default => ['teacher_requests'],
        };
    }

    public static function canAccessView(?string $role, string $view): bool
    {
        return in_array($view, self::allowedViews($role), true);
    }

    /**
     * @return list<array{id: string, label: string, icon: string, group: string}>
     */
    public static function navigationFor(?string $role): array
    {
        $items = config('senaistock.navigation_items', []);
        $allowed = self::allowedViews($role);

        return array_values(array_filter(
            $items,
            fn (array $item) => in_array($item['id'], $allowed, true)
        ));
    }

    public static function can(?string $role, string $ability): bool
    {
        if ($role === self::COORDENADOR) {
            return true;
        }

        $matrix = [
            self::PROFESSOR => [
                'teacher_requests.create' => true,
                'teacher_requests.fulfill' => false,
                'teacher_requests.purchase' => false,
                'purchases.create' => false,
                'purchases.approve' => false,
                'purchases.deliver' => false,
                'stock.receive' => false,
                'stock.withdraw' => false,
                'stock.store_new' => false,
                'alerts.purchase' => false,
                'people.manage' => false,
                'classes.manage' => false,
                'suppliers.manage' => false,
            ],
        ];

        return $matrix[$role][$ability] ?? false;
    }

    public static function authorize(Request $request, string $ability): void
    {
        $role = self::fromSession($request);

        if (! self::can($role, $ability)) {
            abort(403, 'Seu cargo não tem permissão para esta ação.');
        }
    }

    public static function authorizeRole(Request $request, string ...$roles): void
    {
        $role = self::fromSession($request);

        if ($role !== self::COORDENADOR && ! in_array($role, $roles, true)) {
            abort(403, 'Seu cargo não tem permissão para acessar este recurso.');
        }
    }

    /**
     * @return array<string, bool>
     */
    public static function permissions(?string $role): array
    {
        $abilities = [
            'teacher_requests.create',
            'teacher_requests.fulfill',
            'teacher_requests.purchase',
            'purchases.create',
            'purchases.approve',
            'purchases.deliver',
            'stock.receive',
            'stock.withdraw',
            'stock.store_new',
            'alerts.purchase',
            'people.manage',
            'classes.manage',
            'suppliers.manage',
        ];

        return collect($abilities)
            ->mapWithKeys(fn (string $ability) => [$ability => self::can($role, $ability)])
            ->all();
    }
}
