<?php namespace MajorMedia\WizardProcess;

use Illuminate\Http\Request;

abstract class Step
{

  public $label;
  public $code;
  public $view;
  public $number;
  public $key;
  public $index;
  protected $wizard;

  public function __construct(int $number, $key, int $index, Wizard $wizard)
  {
    $this->number = $number;
    $this->key = $key;
    $this->index = $index;
    $this->wizard = $wizard;
  }

  abstract public function process(Request $request);

  public function rules(Request $request = null): array
  {
    return [];
  }

  public function saveProgress(Request $request, array $additionalData = [])
  {
    $wizardData = $this->wizard->data();
    $wizardData[$this::$slug] = $request->except('step', '_token');
    $wizardData = array_merge($wizardData, $additionalData);

    $this->wizard->data($wizardData);
  }

  public function clearData(): void
  {
    $data = $this->wizard->data();
    unset($data[$this::$slug]);
    $this->wizard->data($data);
  }
}