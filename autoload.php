<?php
function _tagfeather_namespace_autoload($class) {
    $path=__DIR__.'/src/';
    $namespace='TagFeather\\';
    if (strncmp($namespace, $class, strlen($namespace)) !== 0) {
        return false;
    }
    $file = $path . str_replace('\\', '/', substr($class, strlen($namespace))) . '.php';
    if (!file_exists($file)) {
        return false;
    }
    require_once $file;
    return true;
}
spl_autoload_register('_tagfeather_namespace_autoload');
