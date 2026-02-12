<?php
namespace Majormedia\Stripe\Http;

use Exception;
use Backend\Classes\Controller;
use October\Rain\Support\Facades\Config;

class Webhook extends Controller
{
    static private $eventHandlers = [];

    static public function addEventHandler(string $event, \Closure $closure)
    {
        self::$eventHandlers[$event][] = $closure;
    }

    public function __invoke()
    {
        try {
            $event = \Stripe\Webhook::constructEvent(
                @file_get_contents('php://input'),
                $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? "",
                Config::get('majormedia.stripe::stripe.webhook_secret'),
            );
        } catch (\UnexpectedValueException $e) {
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            http_response_code(400);
            exit();
        }

        // call all registered event handlers for the current event
        if (isset(self::$eventHandlers[$event->type]))
            foreach (self::$eventHandlers[$event->type] as $eventHandler)
                $eventHandler($event->data->object);

        http_response_code(200);
    }
}