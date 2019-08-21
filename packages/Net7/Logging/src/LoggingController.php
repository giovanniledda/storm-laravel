<?php
/**
 * NOT IN USE
 * serve per ottenere la lista delle log
 */
namespace Net7\Logging;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Net7\Logging\models\Logs;

class LoggingController extends Controller
{
    /**
     * restituisce le log per utente o context
     * @param Request $request
     * @return type
     */
    public function index(Request $request) {
      return [];
    }
}
