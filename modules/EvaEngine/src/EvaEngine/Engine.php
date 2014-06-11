<?php

namespace Eva\EvaEngine;

use Phalcon\Mvc\Router;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\DI\FactoryDefault;
use Phalcon\Config;
use Phalcon\Loader;
use Phalcon\Mvc\Application;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Logger\Adapter\File as FileLogger;
use Phalcon\Mvc\Dispatcher;
use Eva\EvaEngine\Mvc\View;
use Eva\EvaEngine\Mvc\Model\Manager as ModelManager;
use Eva\EvaEngine\Tag;

class Engine
{
    protected $appRoot;

    protected $appName; //for cache prefix

    protected $modulesPath;

    protected $di;

    protected $application;

    protected $configPath;

    protected $cacheEnable = false;

    public function setAppRoot($appRoot)
    {
        $this->appRoot = $appRoot;
        return $this;
    }

    public function getAppRoot()
    {
        return $this->appRoot;
    }

    public function setAppName($name)
    {
        $this->appName = $name;
        return $this;
    }

    public function getAppName()
    {
        return $this->appName;
    }

    public function setConfigPath($path)
    {
        $this->configPath = $path;
        return $this;
    }

    public function getConfigPath()
    {
        if ($this->configPath) {
            return $this->configPath;
        }
        return $this->configPath = $this->appRoot . '/config';
    }


    public function setModulesPath($modulesPath)
    {
        $this->modulesPath = $modulesPath;
        return $this;
    }

    public function getModulesPath()
    {
        if ($this->modulesPath) {
            return $this->modulesPath;
        }
        return $this->modulesPath = $this->appRoot . '/modules';
    }

    public function getApplication()
    {
        if ($this->application) {
            return $this->application;
        }

        return $this->application = new Application();
    }

    public function loadModules(array $moduleSettings)
    {
        $moduleManager = $this->getDI()->getModuleManager();
        if(!$this->getDI()->getConfig()->debug) {
            $cachePrefix = $this->getAppName();
            $cacheFile = $this->getConfigPath() . "/_cache.$cachePrefix.modules.php";
            $moduleManager->setCacheFile($cacheFile);
        }
        $moduleManager
            ->setDefaultPath($this->getModulesPath())
            ->loadModules($moduleSettings, $this->getAppName());

        $this->getApplication()->registerModules($moduleManager->getModules());
        $this->getDI()->set('moduleManager', $moduleManager);
        return $this;
    }

    public function readCache($cacheFile)
    {
        if(file_exists($cacheFile) && $cache = include($cacheFile)) {
            return $cache;
        }
        return null;
    }

    public function writeCache($cacheFile, array $content)
    {
        if($cacheFile && $fh = fopen($cacheFile, 'w')) {
            fwrite($fh, '<?php return ' . var_export($content, true) . ';');
            fclose($fh);
            return true;
        }
        return false;
    }

    public function setDI(\Phalcon\DiInterface $di)
    {
        $this->di = $di;
        return $this;
    }

    public function getDI()
    {
        if ($this->di) {
            return $this->di;
        }

        $di = new FactoryDefault();
        $self = $this;

        /**********************************
        DI initialize for MVC core
        ***********************************/

        //call loadmodules will overwrite this
        $di->set('moduleManager', function () use ($di) {
            $moduleManager = new ModuleManager();
            $moduleManager->setEventsManager($di->getEventsManager());
            return $moduleManager;
        }, true);

        //System global events manager
        $di->set('eventsManager', function () {
            return new EventsManager();
        }, true);

        $di->set('config', function () use ($self) {
            return $self->diConfig();
        }, true);

        $di->set('router', function () use ($self) {
            return $self->diRouter();
        }, true);

        $di->set('dispatcher', function () use ($di) {
            $dispatcher = new Dispatcher();
            $dispatcher->setEventsManager($di->getEventsManager());
            return $dispatcher;
        }, true);

        $di->set('modelsMetadata', function () use ($self) {
            return $this->diModelsMetaData();
        });

        $di->set('modelsManager', function () use ($di) {
            //for solving db master/slave under static find method
            $modelsManager = new ModelManager();
            return $modelsManager;
        });

        $di->set('view', function () use ($di) {
            $view = new View();
            $view->setViewsDir(__DIR__ . '/views/');
            $view->setEventsManager($di->getEventsManager());
            return $view;
        });

        $di->set('session', function () use ($self) {
            return $self->diSession();
        });

        /**********************************
        DI initialize for database
        ***********************************/
        $di->set('dbMaster', function () use ($self) {
            return $self->diDbMaster();
        });

        $di->set('dbSlave', function () use ($self) {
            return $self->diDbSlave();
        });

        /**********************************
        DI initialize for cache
        ***********************************/
        $di->set('viewCache', function() use ($self) {
            return $self->diViewCache();
        });

        $di->set('modelCache', function() use ($di) {
            return $self->diModelCache();
        });

        $di->set('apiCache', function() use ($di) {
            return $self->diApiCache();
        });

        /**********************************
        DI initialize for queue
        ***********************************/
        $di->set('queue', function () use ($di) {
            $config = $di->getConfig();
            $client = new \GearmanClient();
            $client->setTimeout(1000);
            foreach ($config->queue->servers as $key => $server) {
                $client->addServer($server->host, $server->port);
            }
            return $client;
        });

        $di->set('worker', function () use ($di) {
            $config = $di->getConfig();
            $worker = new \GearmanWorker();
            foreach ($config->queue->servers as $key => $server) {
                $worker->addServer($server->host, $server->port);
            }
            return $worker;
        });


        /**********************************
        DI initialize for email 
        ***********************************/
        $di->set('mailer', function () use ($self) {
            return $self->diMailer();
        });

        $di->set('mailMessage', 'Eva\EvaEngine\MailMessage');

        /**********************************
        DI initialize for helpers
        ***********************************/
        $di->set('url', function () use ($di) {
            $config = $di->getConfig();
            $url = new UrlResolver();
            $url->setBaseUri($config->baseUri);
            return $url;
        });

        $di->set('escaper', 'Phalcon\Escaper');

        $di->set('tag', function () use ($di) {
            Tag::setDi($di);
            return new Tag();
        });

        $di->set('flash', 'Phalcon\Flash\Session');

        $di->set('placeholder', 'Eva\EvaEngine\View\Helper\Placeholder');

        $di->set('cookies', function () {
            $cookies = new \Phalcon\Http\Response\Cookies();
            $cookies->useEncryption(false);
            return $cookies;
        });

        $di->set('translate', function () use ($di) {
            $config = $di->getConfig();
            $file = $config->translate->path . $config->translate->forceLang . '.csv';
            if (false === file_exists($file)) {
                $file = $config->translate->path . 'empty.csv';
            }
            $translate = new \Phalcon\Translate\Adapter\Csv(array(
                'file' => $file,
                'delimiter' => ',',
            ));

            return $translate;
        });



        $di->set('logException', function () use ($di) {
            $config = $di->get('config');

            return $logger = new FileLogger($config->logger->path . 'error_' . date('Y-m-d') . '.log');
        });




        /*
        $di->set('fileSystem', function () use ($di) {
            $config = $di->get('config');
            $adapter = new \Gaufrette\Adapter\Local();
            $filesystem = new Filesystem($adapter);

            return $filesystem;
        });
        */

        return $this->di = $di;
    }

    public function diConfig()
    {
        $di = $this->getDI();
        $cachePrefix = $this->getAppName();
        $cacheFile = $this->getConfigPath() . "/_cache.$cachePrefix.config.php";
        if($cache = $this->readCache($cacheFile)) {
            return new Config($cache);
        }

        $config = new Config();
        //merge all loaded module configs
        $moduleManager = $di->get('moduleManager');
        if ($moduleManager && $modules = $moduleManager->getModules()) {
            foreach ($modules as $moduleName => $module) {
                $moduleConfig = $moduleManager->getModuleConfig($moduleName);
                if ($moduleConfig instanceof Config) {
                    $config->merge($moduleConfig);
                } else {
                    $config->merge(new Config($moduleConfig));
                }
            }
        }

        //merge config default
        $config->merge(new Config(include $this->getConfigPath() . "/config.default.php"));

        //merge config local
        if (false === file_exists($this->getConfigPath() . "/config.local.php")) {
            return $config;
        }
        $config->merge(new Config(include $this->getConfigPath() . "/config.local.php"));

        if(!$config->debug) {
            $this->writeCache($cacheFile, $config->toArray());
        }
        return $config;
    }

    public function diRouter()
    {
        $di = $this->getDI();
        $cachePrefix = $this->getAppName();
        $cacheFile = $this->getConfigPath() . "/_cache.$cachePrefix.router.php";

        $moduleManager = $di->getModuleManager();
        $config = new Config();
        if ($moduleManager && $modulesArray = $moduleManager->getModules()) {
            foreach ($modulesArray as $moduleName => $module) {
                $config->merge(new Config($moduleManager->getModuleRoutesBackend($moduleName)));
                $config->merge(new Config($moduleManager->getModuleRoutesFrontend($moduleName)));
            }
        }

        //Disable default router
        $router = new Router(false);
        //Last extra slash
        $router->removeExtraSlashes(true);
        $config = $config->toArray();
        foreach ($config as $url => $route) {
            if (count($route) !== count($route, COUNT_RECURSIVE)) {
                if (isset($route['pattern']) && isset($route['paths'])) {
                    $method = isset($route['httpMethods']) ? $route['httpMethods'] : null;
                    $router->add($route['pattern'], $route['paths'], $method);
                } else {
                    throw new Exception\InvalidArgumentException(sprintf('No route pattern and paths found by route %s', $url));
                }
            } else {
                $router->add($url, $route);
            }
        }
        return $router;
    }

    public function diModelsMetaData()
    {
        $adapterMapping = array(
            'apc' => 'Phalcon\Mvc\Model\MetaData\Apc',
            'files' => 'Phalcon\Mvc\Model\MetaData\Files',
            'memory' => 'Phalcon\Mvc\Model\MetaData\Memory',
            'xcache' => 'Phalcon\Mvc\Model\MetaData\Xcache',
            'memcache' => 'Phalcon\Mvc\Model\MetaData\Memcache',
            'redis' => 'Phalcon\Mvc\Model\MetaData\Redis',
            'wincache' => 'Phalcon\Mvc\Model\MetaData\Wincache',
        );

        $config = $this->getDI()->getConfig();
        if(!$config->modelsMetadata->enable) {
            return new \Phalcon\Mvc\Model\MetaData\Memory();
        }

        $adapterKey = strtolower($config->modelsMetadata->adapter);
        if(!isset($adapterMapping[$adapterKey])) {
            throw new Exception\InvalidArgumentException(sprintf('No metadata adapter found by %s', $adapterKey));
        }
        $adapterClass = $adapterMapping[$adapterKey];
        return new $adapterClass($config->modelsMetadata->options->toArray());
    }

    public function diDbMaster()
    {
        $config = $this->getDI()->getConfig();
        if(!isset($config->dbAdapter->master->adapter) || !$config->dbAdapter->master) {
            throw new Exception\InvalidArgumentException(sprintf('No DB Master options found'));
        }
        return $this->diDbAdapter($config->dbAdapter->master->adapter, $config->dbAdapter->master->toArray());
    }

    public function diDbSlave()
    {
        $config = $this->getDI()->getConfig();
        $slaves = $config->dbAdapter->slave;
        $slaveKey = array_rand($slaves->toArray());
        if(!isset($slaves->$slaveKey) || count($slaves) < 1) {
            throw new Exception\InvalidArgumentException(sprintf('No DB slave options found'));
        }
        return $this->diDbAdapter($slaves->$slaveKey->adapter, $slaves->$slaveKey->toArray());
    }


    protected function diDbAdapter($adapterName, array $options)
    {
        $adapterName = strtolower($adapterName);
        $adapterMapping = array(
            'mysql' => 'Phalcon\Db\Adapter\Pdo\Mysql',
            'oracle' => 'Phalcon\Db\Adapter\Pdo\Oracle',
            'postgresql' => 'Phalcon\Db\Adapter\Pdo\Postgresql',
            'sqlite' => 'Phalcon\Db\Adapter\Pdo\Sqlite',
        );

        $options['charset'] = isset($options['charset']) && $options['charset'] ? $options['charset'] : 'utf8';

        if(!isset($adapterMapping[$adapterName])) {
            throw new Exception\InvalidArgumentException(sprintf('No matched DB adapter found by %s', $adapterName));
        }

        $dbAdapter = new $adapterMapping[$adapterName]($options);

        $config = $this->getDI()->getConfig();
        if ($config->debug) {
            $di = $this->getDI();
            $eventsManager = $di->getEventsManager();
            $logger = new FileLogger($config->logger->path . date('Y-m-d') . '.log');

            //database service name hardcore as db
            $eventsManager->attach('db', function ($event, $dbAdapter) use ($logger) {
                if ($event->getType() == 'beforeQuery') {
                    $sqlVariables = $dbAdapter->getSQLVariables();
                    if (count($sqlVariables)) {
                        $query = str_replace(array('%', '?'), array('%%', "'%s'"), $dbAdapter->getSQLStatement());
                        $query = vsprintf($query, $sqlVariables);
                        //
                        $logger->log($query, \Phalcon\Logger::INFO);
                    } else {
                        $logger->log($dbAdapter->getSQLStatement(), \Phalcon\Logger::INFO);
                    }
                }
            });
            $dbAdapter->setEventsManager($eventsManager);
        }
        return $dbAdapter;
    }

    public function diViewCache()
    {
        return $this->diCache('viewCache', 'eva_view_');
    }

    public function diModelCache()
    {
        return $this->diCache('modelCache', 'eva_model_');
    }

    public function diApiCache()
    {
        return $this->diCache('apiCache', 'eva_api_');
    }

    protected function diCache($configKey, $prefix = 'eva_')
    {
        $config = $this->getDI()->getConfig();
        $adapterMapping = array(
            'apc' => 'Phalcon\Cache\Backend\Apc',
            'file' => 'Phalcon\Cache\Backend\File',
            'libmemcached' => 'Phalcon\Cache\Backend\Libmemcached',
            'memcache' => 'Phalcon\Cache\Backend\Memcache',
            'memory' => 'Phalcon\Cache\Backend\Memory',
            'mongo' => 'Phalcon\Cache\Backend\Mongo',
            'xcache' => 'Phalcon\Cache\Backend\Xcache',
            'base64' => 'Phalcon\Cache\Frontend\Base64',
            'data' => 'Phalcon\Cache\Frontend\Data',
            'igbinary' => 'Phalcon\Cache\Frontend\Igbinary',
            'json' => 'Phalcon\Cache\Frontend\Json',
            'none' => 'Phalcon\Cache\Frontend\None',
            'output' => 'Phalcon\Cache\Frontend\Output',
        );

        $frontCacheClassName = strtolower($config->cache->$configKey->frontend->adapter);
        if(!isset($adapterMapping[$frontCacheClassName])) {
            throw new Exception\InvalidArgumentException(sprintf('No cache adapter found by %s', $frontCacheClassName));
        }
        $frontCacheClass = $adapterMapping[$frontCacheClassName];
        $frontCache = new $frontCacheClass(
            $config->cache->$configKey->frontend->options->toArray()
        );

        if(!$config->cache->enable || !$config->cache->$configKey->enable) {
            $cache = new \Eva\EvaEngine\Cache\Backend\Disable($frontCache);
        } else {
            $backendCacheClassName = strtolower($config->cache->$configKey->backend->adapter);
            if(!isset($adapterMapping[$backendCacheClassName])) {
                throw new Exception\InvalidArgumentException(sprintf('No cache adapter found by %s', $backendCacheClassName));
            }
            $backendCacheClass = $adapterMapping[$backendCacheClass];
            $cache = new $backendCacheClass($frontCache, array_merge(
                array(
                    'prefix' => $prefix,
                ),
                $config->cache->$configKey->backend->options->toArray()
            ));
        }
        return $cache;
    }

    public function diMailer()
    {
        $config = $this->getDI()->getConfig();
        if($config->mailer->transport == 'smtp') {
            $transport = \Swift_SmtpTransport::newInstance()
            ->setHost($config->mailer->host)
            ->setPort($config->mailer->port)
            ->setEncryption($config->mailer->encryption)
            ->setUsername($config->mailer->username)
            ->setPassword($config->mailer->password)
            ;
        } else {
            $transport = \Swift_SendmailTransport::newInstance($config->mailer->sendmailCommand);
        }
        $mailer = \Swift_Mailer::newInstance($transport);
        return $mailer;
    }

    public function diSession()
    {
        $adapterMapping = array(
            'files' => 'Phalcon\Session\Adapter\Files',
            'database' => 'Phalcon\Session\Adapter\Database',
            'memcache' => 'Phalcon\Session\Adapter\Memcache',
            'mongo' => 'Phalcon\Session\Adapter\Mongo',
            'redis' => 'Phalcon\Session\Adapter\Redis',
            'handlersocket' => 'Phalcon\Session\Adapter\HandlerSocket',
        );

        $config = $this->getDI()->getConfig();
        $adapterKey = strtolower($config->session->adapter);
        if(!isset($adapterMapping[$adapterKey])) {
            throw new Exception\InvalidArgumentException(sprintf('No session adapter found by %s', $adapterKey));
        }

        $sessionClass = $adapterMapping[$adapterKey];
        $session = new $sessionClass($config->session->options->toArray());
        if (!$session->isStarted()) {
            //NOTICE: Get php warning here, not found reason
            $session->start();
        }
        return $session;
    }

    public function diTranslate()
    {
        $config = $this->getDI()->getConfig();
        $file = $config->translate->path . $config->translate->forceLang . '.csv';
        if (false === file_exists($file)) {
            $file = $config->translate->path . 'empty.csv';
        }
        $translate = new \Phalcon\Translate\Adapter\Csv(array(
            'file' => $file,
            'delimiter' => ',',
        ));
        return $translate;
    }

    public function bootstrap()
    {
        $this->getApplication()->setDI($this->getDI());
        //Error Handler must run before router start
        $this->initErrorHandler(new Error\ErrorHandler);
        return $this;
    }

    public function run()
    {
        $di = $this->getDI();

        $debug = $di->get('config')->debug;
        if ($debug) {
            $debugger = new \Phalcon\Debug();
            $debugger->debugVar($this->getApplication()->getModules(), 'modules');
            $debugger->listen(true, true);
        }

        $response = $this->getApplication()->handle();
        echo $response->getContent();
    }

    public function initErrorHandler(Error\ErrorHandlerInterface $errorHandler)
    {
        if($this->getDI()->get('config')->debug) {
            return $this;
        }

        $errorClass = get_class($errorHandler);
        set_error_handler("$errorClass::errorHandler");
        set_exception_handler("$errorClass::exceptionHandler");
        register_shutdown_function("$errorClass::shutdownHandler");
        return $this;
    }


    public function runCustom()
    {
        $di = $this->getDI();

        $debug = $di->get('config')->debug;
        if ($debug) {
            $debugger = new \Phalcon\Debug();
            $debugger->listen();
        }

        //Roter
        $router = $di['router'];
        $router->handle();

        //Module handle
        $modules = $this->getApplication()->getModules();
        $routeModule = $router->getModuleName();
        if (isset($modules[$routeModule])) {
            $moduleClass = new $modules[$routeModule]['className']();
            $moduleClass->registerAutoloaders();
            $moduleClass->registerServices($di);
        }

        //dispatch
        $dispatcher = $di['dispatcher'];
        $dispatcher->setModuleName($router->getModuleName());
        $dispatcher->setControllerName($router->getControllerName());
        $dispatcher->setActionName($router->getActionName());
        $dispatcher->setParams($router->getParams());

        //view
        $view = $di['view'];
        $view->start();
        $controller = $dispatcher->dispatch();
        //Not able to call render in controller or else will repeat output
        $view->render(
            $dispatcher->getControllerName(),
            $dispatcher->getActionName(),
            $dispatcher->getParams()
        );
        $view->finish();

        //NOTICE: not able to output flash session content
        $response = $di['response'];
        $response->setContent($view->getContent());
        $response->sendHeaders();
        echo $response->getContent();
    }


    public function __construct($appRoot = null, $appName = 'evaengine')
    {
        $this->appRoot = $appRoot ? $appRoot : __DIR__;
        $this->appName = $appName;
    }

}
