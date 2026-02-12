<?php namespace MajorMedia\ToolBox\Traits;

use Event;

trait ModelAliases
{
  public static function guessOptions($key, $lower = false)
  {
    $options = [];
    $oClass = new \ReflectionClass(__CLASS__);
    $consts = $oClass->getConstants();
    foreach ($consts as $const => $val) {
      $matches = [];
      if (preg_match('#^' . $key . '(.*.)#', $const, $matches))
        $options[$lower ? strtolower($matches[1]) : $matches[1]] = $val;
    }
    return $options;
  }

  public function getStatusOptions()
  {
    $options = $this->guessOptions('LABEL_STATUS_');
    Event::fire('majormedia.toolbox::' . strtolower(class_basename($this)) . '.extendStatusOptions', [&$options]);
    ksort($options);
    return $options;
  }

  public function getStatusAliasAttribute()
  {
    $options = $this->getStatusOptions();
    Event::fire('majormedia.toolbox::' . strtolower(class_basename($this)) . '.extendStatusOptions', [&$options]);
    return ($options[$this->status] ?? '-');
  }
}
