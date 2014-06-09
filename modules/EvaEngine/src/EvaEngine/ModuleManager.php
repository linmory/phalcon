<?php

namespace Eva\EvaEngine;

use Phalcon\Loader;

class ModuleManager
{
    protected $modules = array();

    protected $defaultPath;

    protected $loader;

    protected $cachePath;

    protected $eventsManager;

    public function getLoader()
    {
        if($this->loader) {
            return $this->loader;
        }
        return $this->loader = new Loader();
    }

    public function setLoader(Loader $loader)
    {
        $this->loader = $loader;
        return $this;
    }

    public function getDefaultPath()
    {
        return $this->defaultPath;
    }

    public function setDefaultPath($defaultPath)
    {
        $this->defaultPath = $defaultPath;
        return $this;
    }

    public function setCachePath($cachePath)
    {
        $this->cachePath = $cachePath;
        return $this;
    }

    public function getCachePath()
    {
        return $this->cachePath;
    }

    public function getEventsManager()
    {
        return $this->eventsManager;
    }

    public function setEventsManager($eventsManager)
    {
        return $this->eventsManager = $eventsManager;
    }

    public function loadModules(array $moduleSettings)
    {
        $cachePath = $this->getCachePath();
        $cacheFile = $cachePath ? $cachePath . '/_cache.module.php' : '';
        $loader = $this->getLoader();

        if(file_exists($cacheFile) && $cache = include($cacheFile)) {
            $loader->registerNamespaces($cache['namespaces'])->register();
            $loader->registerClasses($cache['classes'])->register();
            $this->modules = $cache['modules'];
            return $this;
        }

        $defaultModuleSetting = array(
            'className' => '',
            'path' => '',  //Module bootstrap file path
            'dir' => '', //Module source codes dir
            'moduleConfig' => '',
            'routesFrontend' => '',
            'routesBackend' => '',
            'listener' => '',
            'namespaces' => '',
        );

        $modules = array();
        $classes = array();
        $namespaces = array();
        $modulesPath = $this->getDefaultPath();
        foreach ($moduleSettings as $key => $module) {
            if (is_array($module)) {
                $moduleKey = ucfirst($key);
                $module = array_merge($defaultModuleSetting, $module);
            } elseif (is_string($module)) {
                $moduleKey = ucfirst($module);
                //Only Module Name means its a Eva Standard module
                $module = array_merge($defaultModuleSetting, array(
                    'className' => "Eva\\$moduleKey\\Module",
                    'path' => "$modulesPath/$moduleKey/Module.php",
                ));
            } else {
                throw new \Exception(sprintf('Module %s load failed by incorrect format', $key));
            }

            $module['className'] = $module['className'] ? $module['className'] : "Eva\\$key\\Module";
            $module['path'] = $module['path'] ? $module['path'] : "$modulesPath/$key/Module.php";
            $module['dir'] = dirname($module['path']);

            //Disabled when value is false
            $module['moduleConfig'] = false === $module['moduleConfig'] || $module['moduleConfig'] ? $module['moduleConfig'] : $module['dir'] . '/config/config.php';
            $module['routesBackend'] = false === $module['routesBackend'] || $module['routesBackend'] ? $module['routesBackend'] : $module['dir'] . '/config/routes.backend.php';
            $module['routesFrontend'] = false === $module['routesFrontend'] || $module['routesFrontend'] ? $module['routesFrontend'] : $module['dir'] . '/config/routes.frontend.php';
            $module['listener'] = false === $module['listener'] || $module['listener'] ? $module['listener'] : $module['dir'] . '/Listener.php';

            $classes[$module['className']] = $module['path'];
            $modules[$moduleKey] = $module;
        }


        $loader->registerClasses($classes)->register();
        foreach($modules as $key => $module) {
            if(method_exists($module['className'], 'registerGlobalAutoloaders')) {
                $namespace = $module['className']::registerGlobalAutoloaders();
                if(is_array($namespace)) {
                    $namespaces += $namespace;
                }
            }
        }
        $loader->registerNamespaces($namespaces)->register();


        $this->modules = $modules;

        if($cacheFile && $fh = fopen($cacheFile, 'w')) {
            fwrite($fh, '<?php return ' . var_export(array(
                    'classes' => $classes,
                    'namespaces' => $namespaces,
                    'modules' => $modules,
            ), true) . ';');
            fclose($fh);
        }

        return $this;
    }

    public function getModules()
    {
        return $this->modules;
    }

    public function getModulePath($moduleName)
    {
        $modules = $this->getModules();
        if (isset($modules[$moduleName]['dir']) && file_exists($modules[$moduleName]['dir'])) {
            return $modules[$moduleName]['dir'];
        }
        return '';
    }

    public function getModuleConfig($moduleName)
    {
        $modules = $this->getModules();
        if (isset($modules[$moduleName]['moduleConfig']) && file_exists($modules[$moduleName]['moduleConfig'])) {
            return include $modules[$moduleName]['moduleConfig'];
        }
        return array();
    }

    public function getModuleRoutesFrontend($moduleName)
    {
        $modules = $this->getModules();
        if (isset($modules[$moduleName]['routesFrontend']) &&  $modules[$moduleName]['routesFrontend'] && file_exists($modules[$moduleName]['routesFrontend'])) {
            return include $modules[$moduleName]['routesFrontend'];
        }
        return array();
    }

    public function getModuleRoutesBackend($moduleName)
    {
        $modules = $this->getModules();
        if (isset($modules[$moduleName]['routesBackend']) && $modules[$moduleName]['routesBackend'] && file_exists($modules[$moduleName]['routesBackend'])) {
            return include $modules[$moduleName]['routesBackend'];
        }
        return array();
    }

    public function getModuleListener($moduleName)
    {
        $modules = $this->getModules();
        if (isset($modules[$moduleName]['listener']) && $modules[$moduleName]['listener'] && file_exists($modules[$moduleName]['listener'])) {
            return include $modules[$moduleName]['listener'];
        }
        return array();
    }

    public function __construct($defaultPath = null)
    {
        if($defaultPath) {
            $this->defaultPath = $defaultPath;
        }
    }
}
