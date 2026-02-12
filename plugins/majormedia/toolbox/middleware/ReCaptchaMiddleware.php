<?php namespace MajorMedia\ToolBox\Middleware;

use Closure;
use MajorMedia\ToolBox\Models\Settings;
use October\Rain\Exception\AjaxException;
use October\Rain\Exception\ApplicationException;
use ReCaptcha\ReCaptcha;

class ReCaptchaMiddleware
{

  /**
   * Run the request filter.
   *
   * @param \Illuminate\Http\Request $request
   * @param \Closure                 $next
   *
   * @return mixed
   */
  public function handle($request, Closure $next)
  {
    if (Settings::get('recaptcha_enabled')) {

      if (empty(Settings::get('recaptcha_secret_key'))) {
        throw new ApplicationException("Le reCaptcha est mal configuré !");
      }

      $recaptcha = new ReCaptcha(Settings::get('recaptcha_secret_key'));

      if (!$request->exists('recaptchaToken') || !$request->exists('recaptchaAction')) {
        throw new AjaxException(['error' => "The values of 'recaptchaToken' and 'recaptchaAction' can not be empty when Ajax request !"]);
      }

      $response = $recaptcha->setExpectedAction($request->input('recaptchaAction'))
        ->setScoreThreshold(0.3)
        ->verify($request->input('recaptchaToken'), $request->ip());

      if (!$response->isSuccess()) {
        throw new AjaxException(['error' => implode("\n", $response->getErrorCodes())]);
      }
    }

    /**
     * Handle request
     */
    return $next($request);
  }

}
