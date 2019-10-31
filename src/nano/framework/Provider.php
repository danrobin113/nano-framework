<?php
/**
 * nano app framework extension
 * 
 * @package nano/nano-framework
 * @version 1.0
 */

namespace nano\framework;

abstract class Provider
{
  /**
   * Get resource from remote
   * 
   * @return mixed
   */
  abstract public function fetch();

  /**
   * Store resource at remote
   * 
   * @param mixed
   * @return bool
   */
  abstract public function store($resource);
}