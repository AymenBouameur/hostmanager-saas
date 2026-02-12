<?php namespace MajorMedia\ToolBox\Components;

use Auth;
use Cms\Classes\ComponentBase;
use MajorMedia\ToolBox\Models\Message;
use MajorMedia\ToolBox\Models\Newsletter;
use MajorMedia\ToolBox\Models\Settings;
use Responsiv\Uploader\Components\FileUploader;
use ValidationException;

class Newsletters extends ComponentBase
{
  public $newsletterModel = null;

  public function componentDetails()
  {
    return [
      'name' => 'Newsletter',
      'description' => ''
    ];
  }

  public function fetchByEmail ($email) {
    return Newsletter::findByEmail($email);
  }
}