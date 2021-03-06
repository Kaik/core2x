<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Bridge\DependencyInjection\ContainerBuilder;

/**
 * ServiceManager class.
 *
 * @deprecated since 1.4.0
 * @see \Symfony\Component\DependencyInjection\ContainerBuilder
 */
class Zikula_ServiceManager extends ContainerBuilder implements ArrayAccess
{
    /**
     * Attach an existing service.
     *
     * @param string  $id      The ID of the service.
     * @param object  $service An already existing object.
     * @param boolean $shared  True if this is a single instance (default).
     *
     * @deprecated since 1.4.0
     * @see \Symfony\Component\DependencyInjection\ContainerBuilder::set()
     *
     * @throws InvalidArgumentException If the service is already registered.
     *
     * @return object $service.
     */
    public function attachService($id, $service, $shared = true)
    {
        $scope = $shared ? self::SCOPE_CONTAINER : self::SCOPE_PROTOTYPE;
        $this->set($id, $service, $scope);

        return $service;
    }

    /**
     * Detach service.
     *
     * Alias for unregister service
     *
     * @param string $id Service ID.
     *
     * @deprecated since 1.4.0
     * @see \Symfony\Component\DependencyInjection\ContainerBuilder::remove()
     *
     * @throws Exception If the $id isn't registered.
     *
     * @return void
     */
    public function detachService($id)
    {
        $this->unregisterService($id);
    }

    /**
     * Register a service definition.
     *
     * This will register the definition as a service.
     *
     * @param string                           $id         Service Id.
     * @param Zikula_ServiceManager_Definition $definition Service definition.
     * @param boolean                          $shared     Shared type.
     *
     * @deprecated since 1.4.0
     * @see \Symfony\Component\DependencyInjection\ContainerBuilder::setDefinition()
     *
     * @throws InvalidArgumentException If service ID is already registered.
     *
     * @return void
     */
    public function registerService($id, Zikula_ServiceManager_Definition $definition, $shared = true)
    {
        if ($shared) {
            $definition->setScope(self::SCOPE_CONTAINER);
        } else {
            $definition->setScope(self::SCOPE_PROTOTYPE);
        }

        return $this->setDefinition($id, $definition);
    }

    /**
     * Unregisters a service.
     *
     * @param string $id The service identifier.
     *
     * @deprecated since 1.4.0
     * @see \Symfony\Component\DependencyInjection\ContainerBuilder::removeDefinition()
     *
     * @throws InvalidArgumentException If the $id isn't registered.
     *
     * @return void
     */
    public function unregisterService($id)
    {
        $this->removeDefinition($id);
    }

    public function get($id, $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
    {
        if ($id === 'request' && isset($GLOBALS['__request'])) {
            return $GLOBALS['__request'];
        }

        return parent::get($id, $invalidBehavior);
    }

    /**
     * Gets a service by identifier.
     *
     * If the service definition exists then the service will be created according to
     * the Definition class.  If it is singleInstance then it will be attached to the
     * service manager and the defintion deleted.  If this a multiple instance then
     * a new service will be instanciated each time it is requested.  If the service
     * exists already it will be returned.
     *
     * @param string $id The service identifier.
     *
     * @deprecated since 1.4.0
     * @see \Symfony\Component\DependencyInjection\ContainerBuilder::get()
     *
     * @throws InvalidArgumentException If no identifier exists.
     *
     * @return object The service.
     */
    public function getService($id)
    {
        return $this->get($id);
    }

    /**
     * True if we have the service $id registered.
     *
     * @param string $id True if the service is registered.
     *
     * @deprecated since 1.4.0
     * @see \Symfony\Component\DependencyInjection\ContainerBuilder::has()
     *
     * @return boolean
     */
    public function hasService($id)
    {
        return $this->has($id);
    }

    /**
     * Return an array of service IDs registered.
     *
     * @param string $prefix Filter service list by prefix, default = '' for no filtering.
     *
     * @return array Non associative array of service IDs.
     */
    public function listServices($prefix = '')
    {
        $list = [];
        foreach ($this->getServiceIds() as $service) {
            if (empty($prefix) || strpos($service, $prefix) === 0) {
                $list[] = $service;
            }
        }

        return $list;
    }

    /**
     * Getter for arguments property.
     *
     * @deprecated since 1.4.0
     * @see \Symfony\Component\DependencyInjection\ParameterBag::all()
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->getParameterBag()->all();
    }

    /**
     * Setter for arguments property.
     *
     * @param array $array Array of id=>value.
     *
     * @deprecated since 1.4.0
     * @see \Symfony\Component\DependencyInjection\ContainerBuilder::setParameter()
     *
     * @return void
     */
    public function setArguments(array $array)
    {
        // todo $this->getParameterBag()->clear();
        foreach ($array as $key => $value) {
            $this->setParameter($key, $value);
        }
    }

    /**
     * Has argument.
     *
     * @param string $id Id.
     *
     * @deprecated since 1.4.0
     * @see \Symfony\Component\DependencyInjection\ContainerBuilder::hasParameter()
     *
     * @return boolean
     */
    public function hasArgument($id)
    {
        return $this->hasParameter($id);
    }

    /**
     * Set one argument.
     *
     * @param string $id    Argument id.
     * @param string $value Argument value.
     *
     * @deprecated since 1.4.0
     * @see \Symfony\Component\DependencyInjection\ContainerBuilder::setParameter()
     *
     * @return void
     */
    public function setArgument($id, $value)
    {
        $this->setParameter($id, $value);
    }

    /**
     * Get argument.
     *
     * @param string $id Argument id.
     *
     * @deprecated since 1.4.0
     * @see \Symfony\Component\DependencyInjection\ContainerBuilder::getParameter()
     *
     * @throws InvalidArgumentException If id is not set.
     *
     * @return mixed
     */
    public function getArgument($id)
    {
        return $this->getParameter($id);
    }

    /**
     * Load multiple arguments.
     *
     * @param array $array Array of id=>$value.
     *
     * @deprecated since 1.4.0
     * @see \Symfony\Component\DependencyInjection\ContainerBuilder::setParameters()
     *
     * @return void
     */
    public function loadArguments(array $array)
    {
        $this->setArguments($array);
    }

    /**
     * Getter for ArrayAccess interface.
     *
     * @param string $id Argument id.
     *
     * @return mixed Argument value.
     */
    public function offsetGet($id)
    {
        return $this->getArgument($id);
    }

    /**
     * Setter for ArrayAccess interface.
     *
     * @param string $id    Argument id.
     * @param mixed  $value Argument value.
     *
     * @return void
     */
    public function offsetSet($id, $value)
    {
        $this->setArgument($id, $value);
    }

    /**
     * Has() method on argument property for ArrayAccess interface.
     *
     * @param string $id Argument id.
     *
     * @return boolean
     */
    public function offsetExists($id)
    {
        return $this->hasArgument($id);
    }

    /**
     * Unset argument by id, implementation for ArrayAccess.
     *
     * @param string $id Id.
     *
     * @return void
     */
    public function offsetUnset($id)
    {
        if ($this->hasArgument($id)) {
            $this->getParameterBag()->set($id, null);
        }
    }
}
