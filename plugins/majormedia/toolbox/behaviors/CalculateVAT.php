<?php namespace MajorMedia\ToolBox\Behaviors;

use October\Rain\Extension\ExtensionBase;

class CalculateVAT extends ExtensionBase
{
  protected $parent;

  public function __construct($parent)
  {
    $this->parent = $parent;
  }

}
