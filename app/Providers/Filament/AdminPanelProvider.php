<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Illuminate\Http\Request;
use Filament\Support\Colors\Color;
use Filament\Http\Middleware\Authenticate;
use App\Filament\Widgets\TransactionOverview;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Log; // Pastikan ini ada
use Filament\Http\Middleware\DisableBladeIconComponents;
use Illuminate\Support\Facades\Auth; // Pastikan ini ada
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

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
                Pages\Dashboard::class, // Pastikan ini mengacu ke Dashboard kustom Anda
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class, // Komentari jika tidak digunakan
                // Widgets\FilamentInfoWidget::class, // Komentari jika tidak digunakan
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

    // --- HAPUS SELURUH METODE authenticated() INI ---
    // public function authenticated(Request $request): ?\Illuminate\Http\RedirectResponse
    // {
    //     $user = Auth::user();
    //     Log::info('User authenticated: ' . $user->email);
    //     $isAdmin = $user->email === 'admin@admin.com';
    //     Log::info('Is admin: ' . ($isAdmin ? 'True' : 'False'));
    //     if ($isAdmin) {
    //         $redirectPath = route('filament.admin.pages.dashboard');
    //         Log::info('Redirecting admin to: ' . $redirectPath);
    //         return redirect()->to($redirectPath);
    //     } else {
    //         $redirectPath = route('pos.home');
    //         Log::info('Redirecting branch user to: ' . $redirectPath);
    //         return redirect()->to($redirectPath);
    //     }
    // }
    // --- AKHIR HAPUS authenticated() ---


    /**
     * Metode ini menentukan URL ke mana user akan diarahkan setelah login berhasil.
     * Ini adalah metode yang LEBIH ANDAL untuk redirect awal setelah login di Filament.
     */
    // public function getLoginRedirectUrl(): string
    // {
    //     $user = Auth::user();
    //     if ($user->isAdmin()) {
    //         return route('filament.admin.pages.dashboard');
    //     }
    //     return route('pos.home');
    // }
}
