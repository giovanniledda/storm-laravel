<?php

namespace Net7\Logging\models;

use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{ 
    protected $table = 'logs';
    
    protected $fillable = [
        'env',
        'message',
        'level',
        'context',
        'user_id'
    ];

    /**
     * generic insert of log
     * @param string $message
     * @param type $level
     * @param type $context
     */

    private static function _insert($message, $level, $context = null)
    {
        $user = \Auth::user();

        if (is_array($context) || is_object($context)) {
            $context = json_encode($context);
        }
        self::create(
            [
                'env' => \App::environment(),
                'message' => $message,
                'level' => $level,
                'context' => $context,
                'user_id' => isset($user->id) ? $user->id : null
            ]
        );
    }

    /**
     * Info logging
     * @param string $message
     * @param type $context
     */
    public static function info($message, $context = null) {
        self::_insert($message, 'INFO', $context);
    }
    
    /**
     * notice logging
     * @param string $message
     * @param type $context
     */
    public static function notice($message, $context = null) { 
        self::_insert($message, 'NOTICE', $context);
    }
    
    /**
     * warning logging
     * @param string $message
     * @param type $context
     */
    public static function warning($message, $context = null) {
        self::_insert($message, 'WARNING', $context);
    }
    
    /**
     * error logging
     * @param string $message
     * @param type $context
     */
    public static function error($message, $context = null) {
        self::_insert($message, 'ERROR', $context);
    }
    /**
     * critical logging
     * @param string $message
     * @param type $context
     */
    public static function critical($message, $context = null) {
        self::_insert($message, 'CRITICAL', $context);
    }
    /**
     * alert logging
     * @param string $message
     * @param type $context
     */
    public static function alert($message, $context = null) {
        self::_insert($message, 'ALERT', $context);
    }
    
    /**
     * emergency logging
     * @param string $message
     * @param type $context
     */
    public static function emergency($message, $context = null) {
        self::_insert($message, 'EMERGENCY', $context);
    }
    
}
