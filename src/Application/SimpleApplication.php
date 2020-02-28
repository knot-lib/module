<?php
namespace KnotLib\Module\Application;

use KnotLib\Kernel\Exception\ModuleInstallationException;
use KnotLib\Kernel\Kernel\AbstractApplication;
use KnotLib\Kernel\FileSystem\Dir;

use KnotLib\Kernel\Kernel\ApplicationInterface;
use KnotLib\Kernel\Module\ModuleInterface;
use KnotLib\Module\ModuleDependencyResolver;
use Stk2k\File\File;

abstract class SimpleApplication extends AbstractApplication implements ApplicationInterface
{
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

            if (file_exists($dependency_cache)){
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

                $source_code = "<?php" . PHP_EOL . "return " . var_export($this->getResolvedModules(), true) . ";";

                (new File($dependency_cache))->getParent()->makeDirectory();

                $res = file_put_contents($dependency_cache, $source_code);
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