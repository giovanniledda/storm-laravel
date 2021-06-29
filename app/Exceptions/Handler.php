<?php

namespace App\Exceptions;

use App\Utils\Utils as StormUtils;
use CloudCreativity\LaravelJsonApi\Exceptions\HandlesErrors;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use Throwable;

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
     * @param  \Throwable  $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        if ($exception instanceof QueryException) {
            StormUtils::catchIntegrityContraintViolationException($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        if ($this->isJsonApi($request, $exception)) {

            //   $internal_error = StormUtils::convertMessageToInternalErrorCode($message);
            //  return StormUtils::jsonAbortWithInternalError($code, $internal_error, null, $message);

            return $this->renderJsonApi($request, $exception); // return json_api()->response()->exception($e);
        }

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
