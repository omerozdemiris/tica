<?php

namespace App\Providers;

use App\Models\AdminRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

use App\Models\Template;
use Illuminate\Support\Facades\File;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Admin senkronizasyonundan hariç tutulacak rota adları.
     *
     * Örn: ['admin.auth.login', 'admin.login.submit']
     *
     * @var array<int, string>
     */
    protected array $excludedAdminRoutes = [];

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
        $this->excludedAdminRoutes = config('admin.excluded_admin_routes');

        $this->app->booted(function () {
            if (!Schema::hasTable('routes')) {
                return;
            }

            $extractGroup = function (?string $routeName): ?string {
                if (!$routeName) {
                    return null;
                }

                $name = Str::startsWith($routeName, 'admin.')
                    ? Str::after($routeName, 'admin.')
                    : $routeName;

                return Str::before($name, '.') ?: $name;
            };

            $excluded = $this->excludedAdminRoutes;

            $adminRoutes = collect(Route::getRoutes())
                ->filter(function ($route) {
                    $name = $route->getName();
                    return $name && Str::startsWith($name, 'admin.');
                })
                ->reject(function ($route) use ($excluded, $extractGroup) {
                    $group = $extractGroup($route->getName());
                    return $group && in_array($group, $excluded, true);
                })
                ->groupBy(function ($route) use ($extractGroup) {
                    return $extractGroup($route->getName());
                });

            foreach ($adminRoutes as $group => $routes) {
                if (!$group) {
                    continue;
                }

                $route = $routes->first();
                AdminRoute::updateOrCreate(
                    ['name' => $group],
                    [
                        'uri' => $route->uri(),
                        'method' => implode('|', $route->methods()),
                    ]
                );
            }

            // Sync templates
            try {
                if (Schema::hasTable('templates')) {
                    $themesPath = public_path('assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'themes');

                    if (File::isDirectory($themesPath)) {
                        $directories = File::directories($themesPath);
                        foreach ($directories as $dir) {
                            $themeName = basename($dir);

                            $files = File::files($dir);

                            $images = collect($files)
                                ->filter(function ($file) {
                                    return in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                                })
                                ->map(function ($file) {
                                    return $file->getFilename();
                                })
                                ->values()
                                ->toArray();

                            if (!empty($images)) {
                                Template::updateOrCreate(
                                    ['path' => $themeName],
                                    [
                                        'title' => Str::headline($themeName),
                                        'images' => $images,
                                    ]
                                );
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Sessizce geç
            }
        });

        $isMobile = preg_match('/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i', request()->userAgent());

        /* sql enjection olayını gelen request de engellemek için yazılmıştır her inputta kullanılmaktadır. */
        Validator::extend('special_characters', function ($attribute, $value, $parameters, $validator) {
            return !preg_match('/<|>|select|insert|update|delete|where|echo|print_r|var_dump|dd|dump|php|<script|<sql|{{/i', $value);
        });
        /* Request den gelen  TC kimlik numarası için gerekli validate kontrolü. */
        Validator::extend('valid_tc', function ($attribute, $value, $parameters, $validator) {
            $data = TcKimlik::verify($value);
            return $data;
        });
        view()->share('isMobile', (bool) $isMobile);
    }
}
