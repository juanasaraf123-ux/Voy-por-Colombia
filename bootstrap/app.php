<?php

use App\Http\Middleware\EnsureActiveUser;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            SecurityHeaders::class,
        ]);
        $middleware->alias(['active' => EnsureActiveUser::class]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Páginas de error con el diseño del sitio (React) en lugar de las de Laravel.
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            $status = $response->getStatusCode();
            if (!app()->hasDebugModeEnabled() || in_array($status, [403, 404, 419], true)) {
                if (in_array($status, [403, 404, 419, 429, 500, 503], true) && !$request->expectsJson()) {
                    // Las URL inexistentes no pasan por el middleware: se comparten aquí los datos del sitio.
                    Inertia::share(app(HandleInertiaRequests::class)->share($request));
                    return Inertia::render('Error', ['status' => $status])->toResponse($request)->setStatusCode($status);
                }
            }
            if ($status === 419) {
                return back()->with('error', 'La sesión expiró. Inténtalo de nuevo.');
            }
            return $response;
        });
    })->create();
