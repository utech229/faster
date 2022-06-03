<?php

namespace ContainerQ4feEl0;
include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'doctrine'.\DIRECTORY_SEPARATOR.'persistence'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'Persistence'.\DIRECTORY_SEPARATOR.'ObjectManager.php';
include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'doctrine'.\DIRECTORY_SEPARATOR.'orm'.\DIRECTORY_SEPARATOR.'lib'.\DIRECTORY_SEPARATOR.'Doctrine'.\DIRECTORY_SEPARATOR.'ORM'.\DIRECTORY_SEPARATOR.'EntityManagerInterface.php';
include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'doctrine'.\DIRECTORY_SEPARATOR.'orm'.\DIRECTORY_SEPARATOR.'lib'.\DIRECTORY_SEPARATOR.'Doctrine'.\DIRECTORY_SEPARATOR.'ORM'.\DIRECTORY_SEPARATOR.'EntityManager.php';

class EntityManager_9a5be93 extends \Doctrine\ORM\EntityManager implements \ProxyManager\Proxy\VirtualProxyInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager|null wrapped object, if the proxy is initialized
     */
    private $valueHolder55b24 = null;

    /**
     * @var \Closure|null initializer responsible for generating the wrapped object
     */
    private $initializer93f2c = null;

    /**
     * @var bool[] map of public properties of the parent class
     */
    private static $publicPropertiesc50b9 = [
        
    ];

    public function getConnection()
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'getConnection', array(), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->getConnection();
    }

    public function getMetadataFactory()
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'getMetadataFactory', array(), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->getMetadataFactory();
    }

    public function getExpressionBuilder()
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'getExpressionBuilder', array(), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->getExpressionBuilder();
    }

    public function beginTransaction()
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'beginTransaction', array(), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->beginTransaction();
    }

    public function getCache()
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'getCache', array(), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->getCache();
    }

    public function transactional($func)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'transactional', array('func' => $func), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->transactional($func);
    }

    public function wrapInTransaction(callable $func)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'wrapInTransaction', array('func' => $func), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->wrapInTransaction($func);
    }

    public function commit()
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'commit', array(), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->commit();
    }

    public function rollback()
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'rollback', array(), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->rollback();
    }

    public function getClassMetadata($className)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'getClassMetadata', array('className' => $className), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->getClassMetadata($className);
    }

    public function createQuery($dql = '')
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'createQuery', array('dql' => $dql), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->createQuery($dql);
    }

    public function createNamedQuery($name)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'createNamedQuery', array('name' => $name), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->createNamedQuery($name);
    }

    public function createNativeQuery($sql, \Doctrine\ORM\Query\ResultSetMapping $rsm)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'createNativeQuery', array('sql' => $sql, 'rsm' => $rsm), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->createNativeQuery($sql, $rsm);
    }

    public function createNamedNativeQuery($name)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'createNamedNativeQuery', array('name' => $name), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->createNamedNativeQuery($name);
    }

    public function createQueryBuilder()
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'createQueryBuilder', array(), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->createQueryBuilder();
    }

    public function flush($entity = null)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'flush', array('entity' => $entity), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->flush($entity);
    }

    public function find($className, $id, $lockMode = null, $lockVersion = null)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'find', array('className' => $className, 'id' => $id, 'lockMode' => $lockMode, 'lockVersion' => $lockVersion), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->find($className, $id, $lockMode, $lockVersion);
    }

    public function getReference($entityName, $id)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'getReference', array('entityName' => $entityName, 'id' => $id), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->getReference($entityName, $id);
    }

    public function getPartialReference($entityName, $identifier)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'getPartialReference', array('entityName' => $entityName, 'identifier' => $identifier), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->getPartialReference($entityName, $identifier);
    }

    public function clear($entityName = null)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'clear', array('entityName' => $entityName), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->clear($entityName);
    }

    public function close()
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'close', array(), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->close();
    }

    public function persist($entity)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'persist', array('entity' => $entity), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->persist($entity);
    }

    public function remove($entity)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'remove', array('entity' => $entity), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->remove($entity);
    }

    public function refresh($entity)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'refresh', array('entity' => $entity), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->refresh($entity);
    }

    public function detach($entity)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'detach', array('entity' => $entity), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->detach($entity);
    }

    public function merge($entity)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'merge', array('entity' => $entity), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->merge($entity);
    }

    public function copy($entity, $deep = false)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'copy', array('entity' => $entity, 'deep' => $deep), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->copy($entity, $deep);
    }

    public function lock($entity, $lockMode, $lockVersion = null)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'lock', array('entity' => $entity, 'lockMode' => $lockMode, 'lockVersion' => $lockVersion), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->lock($entity, $lockMode, $lockVersion);
    }

    public function getRepository($entityName)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'getRepository', array('entityName' => $entityName), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->getRepository($entityName);
    }

    public function contains($entity)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'contains', array('entity' => $entity), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->contains($entity);
    }

    public function getEventManager()
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'getEventManager', array(), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->getEventManager();
    }

    public function getConfiguration()
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'getConfiguration', array(), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->getConfiguration();
    }

    public function isOpen()
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'isOpen', array(), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->isOpen();
    }

    public function getUnitOfWork()
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'getUnitOfWork', array(), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->getUnitOfWork();
    }

    public function getHydrator($hydrationMode)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'getHydrator', array('hydrationMode' => $hydrationMode), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->getHydrator($hydrationMode);
    }

    public function newHydrator($hydrationMode)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'newHydrator', array('hydrationMode' => $hydrationMode), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->newHydrator($hydrationMode);
    }

    public function getProxyFactory()
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'getProxyFactory', array(), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->getProxyFactory();
    }

    public function initializeObject($obj)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'initializeObject', array('obj' => $obj), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->initializeObject($obj);
    }

    public function getFilters()
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'getFilters', array(), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->getFilters();
    }

    public function isFiltersStateClean()
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'isFiltersStateClean', array(), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->isFiltersStateClean();
    }

    public function hasFilters()
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'hasFilters', array(), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return $this->valueHolder55b24->hasFilters();
    }

    /**
     * Constructor for lazy initialization
     *
     * @param \Closure|null $initializer
     */
    public static function staticProxyConstructor($initializer)
    {
        static $reflection;

        $reflection = $reflection ?? new \ReflectionClass(__CLASS__);
        $instance   = $reflection->newInstanceWithoutConstructor();

        \Closure::bind(function (\Doctrine\ORM\EntityManager $instance) {
            unset($instance->config, $instance->conn, $instance->metadataFactory, $instance->unitOfWork, $instance->eventManager, $instance->proxyFactory, $instance->repositoryFactory, $instance->expressionBuilder, $instance->closed, $instance->filterCollection, $instance->cache);
        }, $instance, 'Doctrine\\ORM\\EntityManager')->__invoke($instance);

        $instance->initializer93f2c = $initializer;

        return $instance;
    }

    protected function __construct(\Doctrine\DBAL\Connection $conn, \Doctrine\ORM\Configuration $config, \Doctrine\Common\EventManager $eventManager)
    {
        static $reflection;

        if (! $this->valueHolder55b24) {
            $reflection = $reflection ?? new \ReflectionClass('Doctrine\\ORM\\EntityManager');
            $this->valueHolder55b24 = $reflection->newInstanceWithoutConstructor();
        \Closure::bind(function (\Doctrine\ORM\EntityManager $instance) {
            unset($instance->config, $instance->conn, $instance->metadataFactory, $instance->unitOfWork, $instance->eventManager, $instance->proxyFactory, $instance->repositoryFactory, $instance->expressionBuilder, $instance->closed, $instance->filterCollection, $instance->cache);
        }, $this, 'Doctrine\\ORM\\EntityManager')->__invoke($this);

        }

        $this->valueHolder55b24->__construct($conn, $config, $eventManager);
    }

    public function & __get($name)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, '__get', ['name' => $name], $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        if (isset(self::$publicPropertiesc50b9[$name])) {
            return $this->valueHolder55b24->$name;
        }

        $realInstanceReflection = new \ReflectionClass('Doctrine\\ORM\\EntityManager');

        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHolder55b24;

            $backtrace = debug_backtrace(false, 1);
            trigger_error(
                sprintf(
                    'Undefined property: %s::$%s in %s on line %s',
                    $realInstanceReflection->getName(),
                    $name,
                    $backtrace[0]['file'],
                    $backtrace[0]['line']
                ),
                \E_USER_NOTICE
            );
            return $targetObject->$name;
        }

        $targetObject = $this->valueHolder55b24;
        $accessor = function & () use ($targetObject, $name) {
            return $targetObject->$name;
        };
        $backtrace = debug_backtrace(true, 2);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $returnValue = & $accessor();

        return $returnValue;
    }

    public function __set($name, $value)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, '__set', array('name' => $name, 'value' => $value), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        $realInstanceReflection = new \ReflectionClass('Doctrine\\ORM\\EntityManager');

        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHolder55b24;

            $targetObject->$name = $value;

            return $targetObject->$name;
        }

        $targetObject = $this->valueHolder55b24;
        $accessor = function & () use ($targetObject, $name, $value) {
            $targetObject->$name = $value;

            return $targetObject->$name;
        };
        $backtrace = debug_backtrace(true, 2);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $returnValue = & $accessor();

        return $returnValue;
    }

    public function __isset($name)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, '__isset', array('name' => $name), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        $realInstanceReflection = new \ReflectionClass('Doctrine\\ORM\\EntityManager');

        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHolder55b24;

            return isset($targetObject->$name);
        }

        $targetObject = $this->valueHolder55b24;
        $accessor = function () use ($targetObject, $name) {
            return isset($targetObject->$name);
        };
        $backtrace = debug_backtrace(true, 2);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $returnValue = $accessor();

        return $returnValue;
    }

    public function __unset($name)
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, '__unset', array('name' => $name), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        $realInstanceReflection = new \ReflectionClass('Doctrine\\ORM\\EntityManager');

        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHolder55b24;

            unset($targetObject->$name);

            return;
        }

        $targetObject = $this->valueHolder55b24;
        $accessor = function () use ($targetObject, $name) {
            unset($targetObject->$name);

            return;
        };
        $backtrace = debug_backtrace(true, 2);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $accessor();
    }

    public function __clone()
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, '__clone', array(), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        $this->valueHolder55b24 = clone $this->valueHolder55b24;
    }

    public function __sleep()
    {
        $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, '__sleep', array(), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;

        return array('valueHolder55b24');
    }

    public function __wakeup()
    {
        \Closure::bind(function (\Doctrine\ORM\EntityManager $instance) {
            unset($instance->config, $instance->conn, $instance->metadataFactory, $instance->unitOfWork, $instance->eventManager, $instance->proxyFactory, $instance->repositoryFactory, $instance->expressionBuilder, $instance->closed, $instance->filterCollection, $instance->cache);
        }, $this, 'Doctrine\\ORM\\EntityManager')->__invoke($this);
    }

    public function setProxyInitializer(\Closure $initializer = null) : void
    {
        $this->initializer93f2c = $initializer;
    }

    public function getProxyInitializer() : ?\Closure
    {
        return $this->initializer93f2c;
    }

    public function initializeProxy() : bool
    {
        return $this->initializer93f2c && ($this->initializer93f2c->__invoke($valueHolder55b24, $this, 'initializeProxy', array(), $this->initializer93f2c) || 1) && $this->valueHolder55b24 = $valueHolder55b24;
    }

    public function isProxyInitialized() : bool
    {
        return null !== $this->valueHolder55b24;
    }

    public function getWrappedValueHolderValue()
    {
        return $this->valueHolder55b24;
    }
}

if (!\class_exists('EntityManager_9a5be93', false)) {
    \class_alias(__NAMESPACE__.'\\EntityManager_9a5be93', 'EntityManager_9a5be93', false);
}
