<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Response as BaseResponse;

class ResponseMacroService extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
         // Custom response macro shortcut for sending success json response
         Response::macro('success', function ($data, $message, $code = BaseResponse::HTTP_OK) {
            return Response::json([
                'status' => true,
                'data' => $data,
                'message' => $message,
            ], $code);
        });

        // Custom response macro shortcut for sending failed json response and logging
        Response::macro('failed', function (
            \Throwable $exception,
            $data = null,
            $message = '',
            $code = BaseResponse::HTTP_UNPROCESSABLE_ENTITY
        ) {
            // Write the exception to log, make sure to define LOG_STACK key in .env
            // and set LOG_CHANNEL=custom_stack
            \Log::write('debug', $exception->getMessage(), $exception->getTrace());

            return Response::json([
                'status' => false,
                'data' => $data,
                'message' => $message ?: $exception->getMessage(),
            ], $code);
        });
    }
}
