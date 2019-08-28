<?php

namespace App\Exceptions;

use CloudCreativity\LaravelJsonApi\Exceptions\HandlesErrors;
use Exception;
use const HTTP_412_DEL_UPD_ERROR_MSG;
use const HTTP_412_EXCEPTION_ERROR_MSG;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Str;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use App\Utils\Utils as StormUtils;


class Handler extends ExceptionHandler
{

    use HandlesErrors;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
        JsonApiException::class,

    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if ($exception instanceof QueryException) {
            if (Str::contains($exception->getMessage(), "Integrity constraint violation")) {
                if (Str::contains($exception->getMessage(), "1451")) {
                    abort(412, __(HTTP_412_DEL_UPD_ERROR_MSG));
                }
                if (Str::contains($exception->getMessage(), "1452")) {
                    abort(412, __(HTTP_412_ADD_UPD_ERROR_MSG));
                }
            }
            abort(412, __(HTTP_412_EXCEPTION_ERROR_MSG, ['exc_msg' => $exception->getMessage()]));
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, \Exception $exception)
    {
        $message = method_exists (  $exception , 'getMessage' ) ? $exception->getMessage() : 'generic error';
        $code    = method_exists (  $exception , 'getStatusCode' ) ? $exception->getStatusCode() : 100;
        /*
        if ($this->isJsonApi($request, $exception)) {
            
            $internal_error = StormUtils::convertMessageToInternalErrorCode($message);
            return StormUtils::jsonAbortWithInternalError($code, $internal_error, null, $message);

//            return $this->renderJsonApi($request, $exception); // return json_api()->response()->exception($e);
        } */
        return parent::render($request, $exception);
    }

    protected function prepareException(Exception $e)
    {
        if ($e instanceof JsonApiException) {
            return $this->prepareJsonApiException($e);
        }

        return parent::prepareException($e);
    }
}
