<?php

namespace App\Support;

use App\Models\Facility;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class SelectedFacility
{
    public const SESSION_ID_KEY = 'facility_id';

    public const SESSION_NAME_KEY = 'facility_name';

    public static function remember(Facility|int $facility): void
    {
        $model = $facility instanceof Facility
            ? $facility
            : Facility::query()->find($facility);

        if (! $model) {
            return;
        }

        session([
            self::SESSION_ID_KEY => (int) $model->id,
            self::SESSION_NAME_KEY => (string) $model->name,
        ]);
    }

    public static function forget(): void
    {
        session()->forget([self::SESSION_ID_KEY, self::SESSION_NAME_KEY]);
    }

    public static function userCanChooseFacility(?User $user = null): bool
    {
        $user = $user ?? auth()->user();

        return $user
            && method_exists($user, 'hasRole')
            && $user->hasRole(['admin', 'super-admin', 'rdhr']);
    }

    public static function forcedFacilityIdForUser(?User $user = null): ?int
    {
        $user = $user ?? auth()->user();

        if (! $user || ! method_exists($user, 'hasRole')) {
            return null;
        }

        if (! $user->hasRole(['facility-admin', 'facility-dsd', 'don', 'facility-ssd', 'ssd', 'facility-editor'])) {
            return null;
        }

        if ($user->facility_id) {
            return (int) $user->facility_id;
        }

        $employee = method_exists($user, 'resolvedBpEmployee')
            ? $user->resolvedBpEmployee(['currentAssignment'])
            : null;

        if ($employee?->currentAssignment?->facility_id) {
            return (int) $employee->currentAssignment->facility_id;
        }

        return null;
    }

    public static function userCanAccessFacility(?User $user, Facility|int $facility): bool
    {
        $user = $user ?? auth()->user();
        $facilityId = $facility instanceof Facility ? (int) $facility->id : (int) $facility;

        if (! $user || $facilityId <= 0) {
            return false;
        }

        if (self::userCanChooseFacility($user)) {
            return Facility::query()->whereKey($facilityId)->exists();
        }

        $forced = self::forcedFacilityIdForUser($user);

        return $forced === null || $forced === $facilityId;
    }

    /**
     * Persist facility from the current request (route/query), when explicitly provided.
     */
    public static function captureFromRequest(?Request $request = null): ?int
    {
        $request = $request ?? request();
        $user = $request?->user();

        if (! $user) {
            return null;
        }

        $explicitId = self::explicitFacilityIdFromRequest($request);

        if ($explicitId && self::userCanAccessFacility($user, $explicitId)) {
            self::remember($explicitId);

            return $explicitId;
        }

        if (! session()->has(self::SESSION_ID_KEY)) {
            $forced = self::forcedFacilityIdForUser($user);
            if ($forced && self::userCanAccessFacility($user, $forced)) {
                self::remember($forced);
            }
        }

        return session(self::SESSION_ID_KEY) ? (int) session(self::SESSION_ID_KEY) : null;
    }

    public static function id(?Request $request = null): ?int
    {
        $request = $request ?? request();
        $user = $request->user();

        $explicitId = self::explicitFacilityIdFromRequest($request);
        if ($explicitId && ($user === null || self::userCanAccessFacility($user, $explicitId))) {
            return $explicitId;
        }

        $sessionId = session(self::SESSION_ID_KEY);
        if ($sessionId && ($user === null || self::userCanAccessFacility($user, (int) $sessionId))) {
            return (int) $sessionId;
        }

        $forced = self::forcedFacilityIdForUser($user);
        if ($forced && self::userCanAccessFacility($user, $forced)) {
            return $forced;
        }

        return null;
    }

    public static function model(?Request $request = null): ?Facility
    {
        $id = self::id($request);

        return $id ? Facility::query()->find($id) : null;
    }

    public static function routeKey(?Request $request = null): ?string
    {
        return self::model($request)?->getRouteKey();
    }

    public static function name(?Request $request = null): ?string
    {
        $facility = self::model($request);

        if ($facility) {
            return $facility->name;
        }

        $sessionName = session(self::SESSION_NAME_KEY);

        return is_string($sessionName) && $sessionName !== '' ? $sessionName : null;
    }

    public static function route(string $name, array $parameters = [], bool $absolute = true): string
    {
        $route = Route::getRoutes()->getByName($name);

        if ($route && in_array('facility', $route->parameterNames(), true) && ! isset($parameters['facility'])) {
            $routeKey = self::routeKey();
            if ($routeKey) {
                $parameters['facility'] = $routeKey;
            }
        }

        return route($name, $parameters, $absolute);
    }

    protected static function explicitFacilityIdFromRequest(?Request $request): ?int
    {
        if (! $request) {
            return null;
        }

        $routeFacility = $request->route('facility');
        if ($routeFacility !== null) {
            $routeId = self::resolveFacilityKey($routeFacility);
            if ($routeId) {
                return $routeId;
            }
        }

        if ($request->filled('facility_id')) {
            return (int) $request->input('facility_id');
        }

        if ($request->filled('facility')) {
            return self::resolveFacilityKey($request->input('facility'));
        }

        return null;
    }

    protected static function resolveFacilityKey(mixed $value): ?int
    {
        if ($value instanceof Facility) {
            return (int) $value->id;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $facility = Facility::query()
            ->where('slug', $value)
            ->orWhere('id', $value)
            ->first();

        return $facility ? (int) $facility->id : null;
    }
}
