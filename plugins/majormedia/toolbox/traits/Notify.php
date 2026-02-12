<?php namespace MajorMedia\ToolBox\Traits;

use Slack;
use SlackMessage;
use October\Rain\Exception\ApplicationException;

trait Notify
{
  private static $ENVIRONEMENTS = [
    'slack' => "Slack",
  ];
  private $isDebug = false;
  private $environement = 'slack';

  private $messages = [
    'debug' => [],
    'user' => [],
  ];

  public function setDebugMode($debug = null)
  {
    $this->isDebug = !is_bool($debug) ? $debug : config('app.debug');
  }

  public function setEnvironement($env)
  {
    if (!in_array($env, static::$ENVIRONEMENTS)) {
      throw new ApplicationException("The environement '$env' does not exists !");
    }
    $this->environement = $env;
  }

  public function notifySlack($abort = false)
  {
    if (!empty(env('WEBHOOK_SLACK', null))) {
      // Use the url you got earlier
      $slack = new Slack(env('WEBHOOK_SLACK'));

      // Create a new message
      $message = new SlackMessage($slack);
      $message->setText($this->getMessages('debug'));

      // Send it!
      if (!$message->send()) {
        // todo: review this, send admin email instead
        throw new ApplicationException("Failed to send Slack Notification 😢");
      }
    }

    if ($abort === true) {
      throw new ApplicationException($this->getMessages());
    }
  }

  // todo: send email notif
  public function notifyMail($abort = false)
  {
    // ...

    if ($abort === true) {
      throw new ApplicationException($this->getMessages());
    }
  }

  public function pushMessage($message, $mode)
  {
    if (!isset($this->messages[$mode])) {
      throw new ApplicationException("The Mode '$mode' not found !");
    }
    $this->messages[$mode][] = $message == '{{GENERAL_MESSAGE}}' ? ($mode == 'debug' ? "An error was occurred !" : ($mode == 'user' ? "Une erreur inconnue est survenue, veuillez réessayer ou nous contacter." : '')) : $message;
  }

  public function getMessages($mode = '')
  {
    switch ($mode = $mode ?: ($this->isDebug ? 'debug' : 'user')) {
      case 'debug':
      case 'user':
        return $this->formatMessages($this->messages[$mode]);
      default:
        throw new ApplicationException("The Mode '$mode' not found !");
    }
  }

  private function formatMessages(array $messages = [])
  {
    return implode("\n", $messages);
  }
}
