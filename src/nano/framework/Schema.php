<?php
/**
 * nano app framework extension
 * 
 * @package nano/nano-framework
 * @version 1.0
 */

namespace nano\framework;

class SchemaField
{
  /**
   * @var string
   */
  var $name;

  /**
   * @var string
   */
  var $default;

  /**
   * @var string
   */
  var $required;

  /**
   * @var string
   */
  var $datatype;

  /**
   * @var string
   */
  var $description;

  /**
   * Construct values
   */
  function __construct($name, $default = null, $required = false, $datatype = 'number', $description = '')
  {
    $this->name = $name;
    $this->default = $default;
    $this->required = $required;
    $this->datatype = $datatype;
    $this->description = $description;
  }
}

/**
 * Schema trait adds value object logic with validation
 */
trait Schema
{
  /**
   * @var SchemaField[]
   */
  private $schema = [];

  /**
   * Construct schema from reflection class
   */
  protected function build()
  {
    // look up class reflection to generate schema
    $rc = new ReflectionClass($this);

    $properties = $rc->getProperties(ReflectionProperty::IS_PUBLIC);

    foreach ($properties as $prop)
    {
      $name = $prop->getName();

      preg_match_all('/@([a-zA-Z][a-zA-Z0-9_]*)(?:\s+\'(.+?)\')?/', $prop->getDocComment(), $args);

      $description = '';
      $required = false;
      $default = null;

      foreach ($args[1] as $col => $arg)
      {
        switch ($arg)
        {
          case 'description':   $description = $args[2][$col]; break;
          case 'required':      $required = true; break;
          case 'default':       $default = $args[2][$col]; break;
        }
      }

      $this->schema[$col] = new SchemaField($name, $default, $required, $description);
    }
  }

  /**
   * load schema from array
   */
  protected function setSchema(array $schema)
  {
    foreach ($schema as $col => $field)
    {
      $this->schema[$col] = new SchemaField($field['name'], @$field['default'], @$field['required'], @$field['datatype'], @$field['description']);
    }
  }

  /**
   * Validate data against schema
   * 
   * @param array
   * @return array|false
   */
  protected function validate(array $data)
  {
    $out = [];

    foreach ($this->schema as $col => $field)
    {
      $value = $data[$col];

      if ($value === null || $value === '') {
        if ($field->required)
          return false;

        $value = $field->default;
      }

      else if (is_numeric($value))
      {
        $value = $value + 0;
      }

      else if (is_bool($value))
      {
        $value = !(!$value);
      }

      // validate final type according to schema datatype
      switch ($field->datatype)
      {
        case 'integer': if (!is_integer($value)) return false;
        case 'float': if (!(is_float($value) || is_double($value))) return false;
        case 'double': if (!(is_float($value) || is_double($value))) return false;
        case 'bool': if (!is_bool($value)) return false;
        case 'string': if (!is_string($value)) return false;
      }

      $out[$field->name] = $value;
    }

    return $out;
  }

  /**
   * TODO: Value object extension?
   */
  // function __get($name)
  // {
    
  // }

  // function __set($name, $value)
  // {
    
  // }
}