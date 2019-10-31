<?php
/**
 * nano app framework extension
 * 
 * @package nano/nano-framework
 * @version 1.0
 */

namespace nano\framework;

use nano\View\Json;

class Persist extends Json
{
  static public $CACHE_DIR = 'cache/';

  /**
   * @var string
   */
  private $persist_filename;

  /**
   * Load from cache if exists
   */
  public function __construct($identifier = '')
  {
    parent::__construct();

    if (!$identifier)
      $identifier = get_class($this);

    $this->persist_filename = self::$CACHE_DIR . "/$identifier.cache.json";

    if (file_exists($this->persist_filename))
      $this->load($this->persist_filename);
  }

  /**
   * Store public scope to cache
   */
  public function __destruct()
  {
    $this->save($this->persist_filename);
  }
}