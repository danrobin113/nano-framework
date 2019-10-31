<?php
/**
 * nano app framework extension
 * 
 * @package nano/nano-framework
 * @version 1.0
 */

namespace nano\framework;

use nano\View\View;

/**
 * The controller class extends a nano\View to add lifecycle hooks
 * framework clients can implement for enhanced behavior. Controllers
 * are added to the context like subcontrollers but are invoked in template
 * by prefixing '@' to the name only for clarity in template code.
 */

class Controller extends View
{
/**
   * Controllers provided by this controller. Each controller element is an array 
   * with parameters: name (index), class and optional props array
   * 
   * @var array[]
   */
  var $controllers = [];

  /**
   * Template for this controller. If string, use it as template, if array,
   * expect an entry 'file' to map an html template file.
   */
  var $template = '';

  /**
   * Hook called immediately after instantiation
   */
  public function created() { }

  /**
   * Hook called immediately before rendering
   */
  public function before() { }

  /**
   * Hook called immediately before destruction
   */
  public function after() { }

  /**
   * Overload reduce() to add new behavior
   */
  public function reduce()
  { 
    $this->content = $this->template;

    if (is_array($this->template))
    {
      if (file_exists(get_include_path() . '/' . $this->template['file']))
        $this->content = file_get_contents($this->template['file'], true);
    }

    foreach ($this->controllers as $name => $controller)
    {
      $class = $controller['class'];
      $props = $controller['props'];

      // TODO: make this lazy by overloading View 'lookup' instead
      $this->context[$name] = new $class($props, $this);
    }

    // call mounted hook
    $this->before();
    
    return parent::reduce();
  }

  /**
   * Overload __construct() to call hooks
   */
  public function __construct(array $props = [], View $parent = null)
  {
    parent::__construct('', [], $parent);

    // call created hook
    $this->created(...array_values($props));
  }

  /**
   * Overload __descruct() to call hooks
   */
  public function __destruct()
  {
    // call vanishing hook
    $this->after();
  }
}