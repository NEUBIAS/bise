<?php

namespace Drupal\Tests\feeds\Traits;

use ReflectionClass;

/**
 * Trait for using reflection in tests.
 */
trait FeedsReflectionTrait {

  /**
   * Gets a ReflectionMethod for a class method.
   *
   * @param string $class
   *   The class to reflect.
   * @param string $name
   *   The method name to reflect.
   *
   * @return \ReflectionMethod
   *   A ReflectionMethod.
   */
  protected function getMethod($class, $name) {
    $class = new ReflectionClass($class);
    $method = $class->getMethod($name);
    $method->setAccessible(TRUE);
    return $method;
  }

  /**
   * Returns a dynamically created closure for the object's method.
   *
   * @param object $object
   *   The object for which to get a closure.
   * @param string $method
   *   The object's method for which to get a closure.
   *
   * @return \Closure
   *   A Closure object.
   */
  protected function getProtectedClosure($object, $method) {
    return $this->getMethod(get_class($object), $method)->getClosure($object);
  }

  /**
   * Calls a protected method on the given object.
   *
   * @param object $object
   *   The object on which to call a protected method.
   * @param string $method
   *   The protected method to call.
   * @param array $args
   *   The arguments to pass to the method.
   *
   * @return mixed
   *   The result of the method call.
   */
  protected function callProtectedMethod($object, $method, array $args = []) {
    $closure = $this->getProtectedClosure($object, $method);
    return call_user_func_array($closure, $args);
  }

}
