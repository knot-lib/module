<?php
declare(strict_types=1);

namespace knotlib\module\application;

use knotlib\kernel\exception\ModuleInstallationException;
use knotlib\kernel\filesystem\FileSystemInterface;
use knotlib\kernel\kernel\AbstractApplication;
use knotlib\kernel\filesystem\Dir;

use knotlib\kernel\kernel\ApplicationInterface;
use knotlib\kernel\module\ModuleInterface;
use knotlib\module\ModuleDependencyResolver;
use stk2k\filesystem\File;
use stk2k\filesystem\FileSystem;

abstract class SimpleApplication extends AbstractApplication implements ApplicationInterface
{
    /** @var bool */
    private $use_dependency_cache;

    /**
     * SimpleApplication constructor.
     *
     * @param bool $use_dependency_cache
     *
     * @param FileSystemInterface|null $filesystem
     */
    public function __construct(FileSystemInterface $filesystem = null, bool $use_dependency_cache = true)
    {
        parent::__construct($filesystem);

        $this->use_dependency_cache = $use_dependency_cache;
    }

    /**
     * Install required modules
     *
     * @throws
     */
    public function install() : ApplicationInterface
    {
        // resolve module dependencies
        if (empty($this->getResolvedModules())){

            $filename = 'dependency.' . sha1(implode("\n",$this->getRequiredModules())) . '.cache.php';
            $dependency_cache = $this->filesystem()->getFile(Dir::CACHE, $filename);

            if ($this->use_dependency_cache && file_exists($dependency_cache)){
                /** @noinspection PhpIncludeInspection */
                $resolved_modules = require($dependency_cache);
                if (!is_array($resolved_modules)){
                    throw new ModuleInstallationException('Dependency cache is broken: ' . $dependency_cache);
                }
                $this->setResolvedModules($resolved_modules);
            }
            else{
                $resolved_modules = (new ModuleDependencyResolver($this->getRequiredModules()))->resolve();

                $this->setResolvedModules($resolved_modules);

                $source_code = "<?php" . PHP_EOL;
                $source_code .= "/*" . PHP_EOL;
                $source_code .= "  Module dependency cache created by kNot Framework" . PHP_EOL;
                $source_code .= "  Application: " . get_class($this) . PHP_EOL;
                $source_code .= "  Created date: " . date('Y-m-d H:i:s') . PHP_EOL;
                $source_code .= "  Required modules: " . PHP_EOL;
                foreach($this->getRequiredModules() as $idx => $module){
                    $source_code .= "        [$idx]$module" . PHP_EOL;
                }
                $source_code .= "*/" . PHP_EOL;
                $source_code .= "return " . var_export($this->getResolvedModules(), true) . ";" . PHP_EOL;

                (new File($dependency_cache))->getParent()->mkdir();

                $res = FileSystem::put($dependency_cache, $source_code);
                if (!$res){
                    throw new ModuleInstallationException('Failed to create dependency cache: ' . $dependency_cache);
                }
            }
        }

        // install modules
        foreach($this->getResolvedModules() as $module){
            $this->installModule($module);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @throws
     */
    public function installModule(string $module_class) : ApplicationInterface
    {
        // skip if the module is already installed
        if (in_array($module_class, $this->getInstalledModules())){
            return $this;
        }

        // create module instance
        $module = null;
        foreach($this->getModuleFactories() as $factory)
        {
            $module = $factory->createModule($module_class, $this);
            if ($module_class instanceof ModuleInterface){
                break;
            }
        }
        if (!$module){
            // module factories did not create instance, so try to create by default constructor.
            $module = new $module_class();
        }

        // install the module
        $module->install($this);
        $this->addInstalledModule($module_class);

        return $this;
    }

}