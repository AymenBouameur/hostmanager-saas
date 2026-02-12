<?php namespace MajorMedia\ToolBox\Traits;

use October\Rain\Exception\ApplicationException;

trait BuildFlashMessage
{

  private $flash_type = '';
  private $flash_message = '';
  private $redirect = '';

  private function buildFlash($type, $message, $redirect = 'home', $redirect_params = [])
  {
    $this->flash_message = $message ?? "Une erreur est survenue lors du traitement de cette demande.";
    $this->flash_type = $type ?? 'error';
    if (request()->ajax()) {
      throw new ApplicationException($this->flash_message);
    }
    $this->redirect = $this->controller->pageUrl($redirect, $redirect_params);
    return true;
  }

  public function checkFlash()
  {
    if ($this->redirect) {
      \Flash::{$this->flash_type}($this->flash_message);
      return redirect()->to($this->redirect);
    }
  }

}
