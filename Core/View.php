<?php

/**
 * App class will call this one in case that a view render is needed.
 */

namespace Core;

defined('APPPATH') or exit('Access denied');

/**
 * App class will call this one in case that a view render is needed.
 */
class View {
    /**
     * Stores the variables passed to the view.
     *
     * @var array
     */
    protected static $data;
    const VIEWS_PATH = '../App/Views/';
    const EXTENSION_TEMPLATES = 'php';

    /**
     * Calls the HTML and send it to the browser.
     *
     * @param string $template Path to the view.
     * @param string $layout   Path to the layout.
     */
    public static function render($template, $layout = null) {
        if (!file_exists(self::VIEWS_PATH.$template.'.'.self::EXTENSION_TEMPLATES)) {
            throw new \Exception('Error: El archivo '.self::VIEWS_PATH.$template.'.'.self::EXTENSION_TEMPLATES.' no existe', 1);
        }
        ob_start();
        extract(self::$data);
        include self::VIEWS_PATH.$template.'.'.self::EXTENSION_TEMPLATES;
        $str = ob_get_contents();
        ob_end_clean();
        if (!$layout) {
            include self::VIEWS_PATH.'layouts/application.php';
        } else {
            include self::VIEWS_PATH.'layouts/'.$layout.'.php';
        }
        unset($_SESSION['flash']);
        unset($_SESSION['error']);
    }

    /**
     * Calls the partial HTML and returns it to the view.
     *
     * @param string $folder  Folder Path.
     * @param string $partial File Path.
     */
    public static function render_partial($folder, $partial) {
        if (!file_exists(self::VIEWS_PATH.$folder.'/_'.$partial.'.'.self::EXTENSION_TEMPLATES)) {
            throw new \Exception('Error: El archivo '.self::VIEWS_PATH.$folder.'/_'.$partial.'.'.self::EXTENSION_TEMPLATES.' no existe', 1);
        }
        ob_start();
        extract(self::$data);
        include self::VIEWS_PATH.$folder.'/_'.$partial.'.'.self::EXTENSION_TEMPLATES;
        $str = ob_get_contents();
        ob_end_clean();
        echo $str;
    }

    public static function get_partial($folder, $partial, $local = null) {
        if (!file_exists(self::VIEWS_PATH.$folder.'/_'.$partial.'.'.self::EXTENSION_TEMPLATES)) {
            throw new \Exception('Error: El archivo '.self::VIEWS_PATH.$folder.'/_'.$partial.'.'.self::EXTENSION_TEMPLATES.' no existe', 1);
        }
        ob_start();
        if (self::$data) {
            extract(self::$data);
        }
        include self::VIEWS_PATH.$folder.'/_'.$partial.'.'.self::EXTENSION_TEMPLATES;
        $str = ob_get_contents();

        ob_end_clean();

        return $str;
    }

    /**
     * Passes a variable down to the view.
     *
     * @param string $name  Name that the variable will have in the view.
     * @param mixed  $value The value of the variable.
     */
    public static function set($name, $value) {
        self::$data[$name] = $value;
    }
}
