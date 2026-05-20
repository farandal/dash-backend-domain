<?php

namespace Domain\App\Providers;

use Laravel\Horizon\Horizon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Response;


class AppDomainServiceProvider extends AppServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {}

    /**
     * Register any  services.
     *
     * @return void
     */
    public function boot()
    {

        App::register(CommandServiceProvider::class);

        Horizon::auth(function () {
            return \in_array(1, Auth::user()->role_ids) ? true : false;
        });

        Response::macro('downloadFile', function ($content, $filename, $contentType) {
            $headers = [
                'Content-type'        => $contentType,
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            return Response::make($content, 200, $headers);
        });


    }
}
