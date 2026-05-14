<?php

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'QrCode' => SimpleSoftwareIO\QrCode\Facades\QrCode::class,

        ]);
        $middleware->preventRequestForgery(except: [
            'https://appoint.chokdev.com/telegram/webhook',
            'http://appoint.chokdev.com/telegram/webhook',

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่'
                ], 419);
            }
            return redirect()
                ->route('login')
                ->with('error', 'เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่');
        });
    })->create();
