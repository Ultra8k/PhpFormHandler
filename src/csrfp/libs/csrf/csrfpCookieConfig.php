<?php
namespace OwaspCsrfProtection;
/**
 * This file has implementation for csrfpCookieConfig class.
 */

if (!defined('__CSRF_PROTECTOR_COOKIE_CONFIG__')) {
    // to avoid multiple declaration errors.
    define('__CSRF_PROTECTOR_COOKIE_CONFIG__', true);

    /**
     * Cookie configuration class.
     */
    class CsrfpCookieConfig
    {
        /**
         * Variable: $path
         * path parameter for setcookie method
         * @var string
         */
        public $path = '';

        /**
         * Variable: $domain
         * domain parameter for setcookie method
         * @var string
         */
        public $domain = '';

        /**
         * Variable: $secure
         * secure parameter for setcookie method
         * @var bool
         */
        public $secure = false;

        /**
         * Variable: $expire
         * expiry parameter in seconds from now for setcookie method, default is
         *  30 minutes
         * @var int
         */
        public $expire = 1800;

        /**
         * Variable: $httponly
         * httponly
         * @var bool
         */
        public $httponly = false;

        /**
         * Variable: $samesite
         * SameSite
         *  Lax
         * @var string
         */
        public $samesite = 'Lax';

        /**
         * Function: constructor
         * 
         * Parameters:
         * @param $cfg - config array loaded from config file;
         */
        function __construct($cfg) {
            if ($cfg !== null) {
                if (isset($cfg['path'])) {
                    $this->path = $cfg['path'];
                }
                
                if (isset($cfg['domain'])) {
                    $this->domain = $cfg['domain'];
                }

                if (isset($cfg['secure'])) {
                    $this->secure = (bool) $cfg['secure'];
                }

                if (isset($cfg['expire']) && $cfg['expire']) {
                    $this->expire = (int)$cfg['expire'];
                }

                if (isset($cfg['httponly']) && $cfg['httponly']) {
                    $this->httponly = (bool)$cfg['httponly'];
                }

                if (isset($cfg['samesite']) && $cfg['samesite']) {
                    $this->samesite = $cfg['samesite'];
                }
            }
        }
    }
}