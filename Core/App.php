<?php

/**
 * An instance of this object will be generated on each request, it handles all the logic and calls the controller/action requested.
 */

namespace Core;

defined('APPPATH') or exit('Access denied');

/**
 * An instance of this object will be generated on each request, it handles all the logic and calls the controller/action requested.
 */
class App
{
    /**
     * The default namespace to look for the controllers.
     */
    const NAMESPACE_CONTROLLERS = "\App\Controllers\\";
    /**
     * stores the called controller.
     *
     * @var string
     */
    private $controller;
    /**
     * stores the called action.
     *
     * @var string
     */
    private $method = 'index';

    /**
     * Stores the parameters passed in the url.
     *
     * @var string
     */
    private $_params = [];

    /**
     * En el constructor cargo la ruta, transformo la url y manejo el request del usuario.
     */
    public function __construct() {
        $this->_controller = $url['controller'];
        $fullClass = self::NAMESPACE_CONTROLLERS.$this->_controller;
        $this->_controller = new $fullClass();
        if (isset($url['action'])) {
            $this->_method = $url['action'];
            if (method_exists($this->_controller, $url['action'])) {
                unset($url['action']);
            } else {
                throw new \Exception("Error Processing Method {$this->_method}", 1);
            }
            $this->_params = $url['params'];
        }
    }

    /**
     * Calls the controller and action that should be executed.
     */
    public function render() {
        call_user_func_array([$this->_controller, $this->_method], $this->_params);
    }
}
