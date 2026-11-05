<?php

namespace App\Services;

use App\Models\Entry;
use App\Models\Media;
use App\Models\Setting;
use App\Models\TravelRoute;
use Illuminate\Support\Facades\DB;

/** Contenido de demostración: conteo y eliminación completa (incluidos sus archivos). */
final class Demo
{
    public static function counts(): array
    {
        return [
            'entries' => Entry::where('is_demo', true)->count(),
            'media' => Media::where('is_demo', true)->count(),
            'routes' => TravelRoute::where('is_demo', true)->count(),
        ];
    }

    public static function remove(): array
    {
        $counts = self::counts();
        DB::transaction(function () {
            TravelRoute::where('is_demo', true)->delete();
            Entry::where('is_demo', true)->delete();
        });
        // Uno por uno para borrar también los archivos del disco.
        Media::where('is_demo', true)->get()->each->delete();
        if ((int) Setting::value('hero_spotlight_id') && !Entry::whereKey((int) Setting::value('hero_spotlight_id'))->exists()) {
            Setting::put('hero_spotlight_id', '');
        }
        if ((int) Setting::value('hero_video_id') && !Media::whereKey((int) Setting::value('hero_video_id'))->exists()) {
            Setting::put('hero_video_id', '');
        }
        return $counts;
    }
}
