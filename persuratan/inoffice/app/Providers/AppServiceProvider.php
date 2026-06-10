<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\SuratMasuk;
use App\Models\Disposisi;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                
                $jumlahBelumDibaca = SuratMasuk::where('status', 'belum_dibaca')->count();
                
                $jumlahDisposisiPending = Disposisi::where('status', 'pending')
                    ->whereHas('penerima', fn($q) => $q->where('users.id', $user->id))
                    ->count();

                $unreadNotificationsCount = \App\Models\Notifikasi::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->count();

                $latestNotifications = \App\Models\Notifikasi::where('user_id', $user->id)
                    ->orderByDesc('created_at')
                    ->limit(5)
                    ->get();
                
                $view->with([
                    'jumlahBelumDibaca' => $jumlahBelumDibaca,
                    'jumlahDisposisiPending' => $jumlahDisposisiPending,
                    'unreadNotificationsCount' => $unreadNotificationsCount,
                    'latestNotifications' => $latestNotifications,
                ]);
            }
        });
    }
}

