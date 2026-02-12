<?php namespace MajorMedia\ToolBox\Components;

use Cms\Classes\ComponentBase;
use Event;
use MajorMedia\ToolBox\Models\Message;
use MajorMedia\ToolBox\Models\Newsletter;
use MajorMedia\ToolBox\Traits\FormatConverter;
use MajorMedia\ToolBox\Traits\GetSetting;
use October\Rain\Exception\ValidationException;
use RainLab\Translate\Classes\Translator;
use Responsiv\Uploader\Components\FileUploader;

class Tools extends ComponentBase
{
  use GetSetting;

  public $messageModel = null;

  public function componentDetails()
  {
    return [
      'name' => 'Tool',
      'description' => ''
    ];
  }

  public function defineProperties()
  {
    return [
      'enableJqueryAutocomplete' => [
        'title' => "Activer le JQuery Autocomplete ?",
        'description' => "",
        'type' => 'checkbox',
        'default' => false,
      ],
      'subscriberForm' => [
        'title' => "Afficher le formulaire Newsletter ?",
        'description' => "",
        'type' => 'checkbox',
        'default' => false,
      ],
      'contactForm' => [
        'title' => "Afficher le formulaire Contact ?",
        'description' => "",
        'type' => 'checkbox',
        'default' => false,
      ],
      'join_file' => [
        'title' => "Joindre des fichiers",
        'description' => "",
        'type' => 'checkbox',
        'default' => false
      ],
    ];
  }

  public function init()
  {
    if ($this->property('join_file') == '1') {
      $fileUploaderComponent = $this->addComponent(FileUploader::class, 'fileUploader', [
        'fileTypes' => '.jpg,.jpeg,.bmp,.png,.gif,.odt,.doc,.docx,.ppt,.pptx,.pdf,.swf,.txt,.xml,.ods,.xls,.xlsx,.wmv,.mp3,.ogg,.wav,.avi,.mov,.mp4,.mpeg,.webm',
        'deferredBinding' => true,
        'placeholderText' => "Fichiers joints"
      ]);
      $fileUploaderComponent->bindModel('files', $this->messageModel = new Message());
      $fileUploaderComponent->isMulti = true;
    }
  }

  public function onRun()
  {
    $this->addCss('assets/css/style.css');
    $this->addJs('assets/js/scripts.js');
    if ($this->property('enableJqueryAutocomplete') == '1') {
      $this->addJs('assets/plugins/autocomplete/js/jquery.autocomplete.js');
      $this->addCss('assets/plugins/autocomplete/css/autocomplete.css');
    }
  }

  public function getCurrentCurrencySymbol()
  {
    return FormatConverter::getCurrentCurrencySymbol();
  }

  public function onRefreshFiles()
  {
    $this->pageCycle();
  }

  public function onSubscribe()
  {
    if (empty(post('email')))
      throw new ValidationException(['email' => "Le champ e-mail ne peut pas être vide!"]);

    $newsletter = new Newsletter();
    $newsletter->name = post('name');
    $newsletter->email = post('email');
    $newsletter->save();
  }

  public function onAskingMessage()
  {
    if (is_null($this->messageModel))
      $this->messageModel = new Message();

    $this->messageModel->full_name = post('full_name');
    $this->messageModel->email = post('email');
    $this->messageModel->company = post('company');
    $this->messageModel->phone = post('phone');
    $this->messageModel->website = post('website');
    $this->messageModel->duration = post('duration');
    $this->messageModel->subject = post('subject');
    $this->messageModel->message = post('message');

    Event::fire('majormedia.toolbox:extendOnAskingMessageFields', [&$this->messageModel]);

    $this->messageModel->save(null, post('_session_key'));
  }
}