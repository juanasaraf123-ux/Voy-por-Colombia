<?php

namespace App\Http\Middleware;

use App\Models\Region;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Throwable;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $s = Setting::allValues();
        $user = $request->hasSession() ? $request->user() : null;
        return array_merge(parent::share($request), [
            'site' => fn () => [
                'name' => $s['site_name'],
                'tagline' => $s['site_tagline'],
                'logoPre' => $s['logo_pre'],
                'logoTitle' => $s['logo_title'],
                'logoSubtitle' => $s['logo_subtitle'],
                'footerText' => $s['footer_text'],
                'contactEmail' => $s['contact_email'],
                'socials' => [
                    'instagram' => $s['social_instagram'], 'facebook' => $s['social_facebook'], 'youtube' => $s['social_youtube'],
                    'tiktok' => $s['social_tiktok'], 'x' => $s['social_x'],
                ],
                'regions' => $this->regions(),
                'tiles' => ['url' => config('vpc.map_tile_url'), 'attribution' => config('vpc.map_tile_attribution')],
            ],
            'auth' => fn () => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
                'roleLabel' => User::ROLES[$user->role] ?? $user->role,
                'permissions' => User::PERMISSIONS[$user->role] ?? [],
            ] : null,
            'flash' => fn () => $request->hasSession() ? [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ] : [],
        ]);
    }

    private function regions(): array
    {
        try {
            return Region::orderBy('sort_order')->get(['slug', 'name', 'color'])->toArray();
        } catch (Throwable) {
            return [];
        }
    }
}
