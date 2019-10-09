<?php

class Autoloader
{

    /**
     * 类库自动加载，写死路径，确保不加载其他文件。
     * @param string $class 对象类名
     * @return void
     */
    public static function autoload($class)
    {
        $name = $class;
        if (false !== strpos($name, '\\')) {
            $name = strstr($class, '\\', true);
        }

        $filename = YIBAI_AUTOLOADER_PATH . '/yibai/' . $name . '.php';
        if (is_file($filename)) {
            include $filename;
            return;
        }

        $filename = YIBAI_AUTOLOADER_PATH . '/yibai/common/' . $name . '.php';
        if (is_file($filename)) {
            include $filename;
            return;
        }

        $filename = YIBAI_AUTOLOADER_PATH . '/yibai/domain/' . $name . '.php';
        if (is_file($filename)) {
            include $filename;
            return;
        }

        $filename = YIBAI_AUTOLOADER_PATH . '/yibai/internal/util' . $name . '.php';
        if (is_file($filename)) {
            include $filename;
            return;
        }

    }
}

spl_autoload_register('Autoloader::autoload');