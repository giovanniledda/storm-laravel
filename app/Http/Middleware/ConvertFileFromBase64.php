<?php

namespace App\Http\Middleware;

use Closure;

class ConvertFileFromBase64
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $requestContent = json_decode ($request->getContent(), true);

        $base64File = @$requestContent['data']['attributes']['file'];
        if ($base64File) {
            $tmpFilename = uniqid('phpfile_') ;
            $tmpFileFullPath = '/tmp/'. $tmpFilename;
            $h = fopen ($tmpFileFullPath, 'w');
            $decoded = base64_decode($base64File);
            fwrite($h, $decoded, strlen($decoded));
            fclose($h);
        }
        if (isset($request->content['file'])){
            unset($request->content['file']);
        }

        return $next($request);
    }
}
