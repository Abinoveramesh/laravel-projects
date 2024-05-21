<?php

namespace App\Base\Helpers;

use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Jobs\ExceptionHandlerJob;

trait ExceptionHandlerModel
{
    public static function sendEmail(Exception $exception)
    {
        return response()->json(0,200);

        try {
            if(config('app.error_handler_mail_config') == 1) {
                $e = FlattenException::create($exception);
                $handler = new SymfonyExceptionHandler();
                $html = $handler->getHtml($e);
                $errorHandlerMailId = config("app.error_handler_mail_id");
                $request = request();
                $apiPath = $request->path();
                try
                {
                    $details= new ExceptionHandlerJob($html , $errorHandlerMailId , $apiPath);
                    dispatch($details);
                }catch(\Exception $e)
                {
                    Log::error('Mail error:: ' . $e->getMessage());
                }
            }
        } catch (Exception $ex) {
            Log::info('Exception mail error ',[$ex]);
        }

        $response = array('status' => false, 'msg' => 'something went wrong');
        return response()->json($response, 200);
    }
}