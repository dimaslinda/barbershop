<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Widgets\TransactionOverview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                TransactionOverview::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                VerifyCsrfToken::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    public function authenticated(Request $request): ?\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();

        // --- DEBUGGING DIMULAI DI SINI ---
        // Menampilkan email user yang login
        Log::info('User authenticated: ' . $user->email);

        // Menampilkan hasil pengecekan admin
        $isAdmin = $user->email === 'admin@admin.com';
        Log::info('Is admin: ' . ($isAdmin ? 'True' : 'False'));

        // Menampilkan redirect path yang akan digunakan
        if ($isAdmin) {
            $redirectPath = route('filament.admin.pages.dashboard');
            Log::info('Redirecting admin to: ' . $redirectPath);
            return redirect()->to($redirectPath);
        } else {
            $redirectPath = route('pos.home');
            Log::info('Redirecting branch user to: ' . $redirectPath);
            return redirect()->to($redirectPath);
        }
        // --- DEBUGGING BERAKHIR DI SINI ---
    }
}
