<?php
declare(strict_types=1);

namespace KnotLib\Module\Application;

use KnotLib\Kernel\FileSystem\Dir;
use KnotLib\Kernel\Kernel\ApplicationInterface;
use KnotLib\Kernel\Module\ModuleInterface;
use KnotLib\Module\Exception\ClassNotFoundException;
use KnotLib\Module\Exception\ModuleException;

final class PluginModuleLoader
{
    const NAMESPACE_SEPARATOR = '\\';
    const PLUGIN_CONFIG_FILENAME = 'plugin_files.php';

    /**
     * Load plugin modules
     *
     * @param ApplicationInterface $app
     *
     * @return bool
     *
     * @throws
     */
    public static function loadPluginModules(ApplicationInterface $app) : bool
    {
        // get plugin dir
        if (!$app->filesystem()->directoryExists(Dir::PLUGIN)){
            return false;
        }
        $plugin_dir = $app->filesystem()->getDirectory(Dir::PLUGIN);

        if (!is_dir($plugin_dir)){
            return false;
        }

        // read plugin_config.json
        $plugin_config_file = $plugin_dir . DIRECTORY_SEPARATOR . self::PLUGIN_CONFIG_FILENAME;

        if (!is_file($plugin_config_file)){
            return false;
        }

        /** @noinspection PhpIncludeInspection */
        $classmap = require($plugin_config_file);
        if (!is_array($classmap)){
            throw new ModuleException('Failed to read plugin config file: ' . $plugin_config_file);
        }

        foreach($classmap as $key => $value)
        {
            // if key is integer, value means class name(FQCN)
            $class_name = is_int($key) ? $value : $key;
            $filename = is_int($key) ? null : $value;

            // load plugin module file
            if ($filename){
                if (!is_string($filename)){
                    throw new ModuleException('Invalid file name detected in plugin config file: ' . $plugin_config_file);
                }
                if (is_file($filename)){
                    // full path is sepcified
                    /** @noinspection PhpIncludeInspection */
                    require $value;
                }
                else{
                    // otherwise, path is considered as relative path to plugin dir
                    $path = "{$plugin_dir}/$filename";
                    if (!is_file($path)){
                        throw new ModuleException('Plugin file not found: ' . $value);
                    }
                    /** @noinspection PhpIncludeInspection */
                    require $path;
                }
            }

            // check class name
            if (!is_string($class_name)){
                throw new ModuleException('Invalid class name detected in plugin config file: ' . $plugin_config_file);
            }
            $plugin_class = str_replace('.', self::NAMESPACE_SEPARATOR, $class_name);
            if (!class_exists($plugin_class)){
                throw new ClassNotFoundException($plugin_class);
            }
            if (!in_array(ModuleInterface::class, class_implements($plugin_class))){
                $msg = sprintf("Plugin class(%s) must implement: %s", $plugin_class, ModuleInterface::class);
                throw new ModuleException($msg);
            }

            // require dependent modules of the plugin module(Confirm setup autoloader of the dependent module class in advance)
            $required_modules = forward_static_call([$plugin_class, 'requiredModules']);
            if (!is_array($required_modules)){
                throw new ModuleException('requiredModules method returns invalid type: ' . $plugin_class);
            }
            foreach($required_modules as $module){
                $app->requireModule($module);
            }

            // requrire the module
            $app->requireModule($plugin_class);
        }

        return true;
    }

}