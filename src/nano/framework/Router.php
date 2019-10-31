<?php
/**
 * nano app framework extension
 * 
 * @package nano/nano-framework
 * @version 1.0
 */

namespace nano\framework;

use nano\Http\Request;
use nano\Routing\Route;
use nano\View\View;

/**
 * Overload API router to dynamically route controller views in application context
 */
class Router extends Controller
{
  /**
   * Routed controller
   * 
   * @var array|null
   */
  private $route = null;

  /**
   * Harness the created hook to inject sub view
   * 
   * @param array $routes, app routes
   */
  public function created($routes = [])
  {
    $request = Request::fromContext();

    foreach ($routes as $pattern => $handler)
    {
      $route = new Route('get', $pattern, $handler, []);

      if ($route->resolves($request))
      {
        if (is_array($handler))
        {
          if (isset($handler['controller']))
          {
            $props = $route->getArguments() ?: [];

            if (isset($handler['props']))
              $props = array_merge($handler['props'], $props);

            $this->route = [
              'class' => $handler['controller'], 
              'props' => $props
            ];
          }

          else if (isset($handler['file']))
          {
            $this->route = [
              'file' => $handler['file']
            ];
          }
        }

        else {
          $this->route = [
            'file' => $handler
          ];
        }

        if (isset($handler['name']))
        {
          $this->route['name'] = $handler['name'];
        }
      }
    }
  }

  /**
   * Overload the reduce function to inject other view
   */
  public function reduce()
  {
    if (!$this->route)
      return '';

    $content = '';

    if (isset($this->route['file'])) {
      if (!\file_exists(get_include_path() . '/' . $this->route['file']))
        return '';

      $content = (new View(
        file_get_contents($this->route['file'], true), 
        [], $this->parent))->reduce();
    }

    else {
      $class = $this->route['class'];
      $props = $this->route['props'];

      $instance = new $class($props, $this->parent);
      $content = $instance->reduce();
    }

    // if a named route is supplied, wrap it with id
    if ($this->route['name'])
      $content = "<div id=\"{$this->route['name']}\">$content</div>";
    
    return $content;
  }
}