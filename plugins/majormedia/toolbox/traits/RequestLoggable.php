<?php namespace MajorMedia\ToolBox\Traits;

use Exception;
use October\Rain\Database\Model;

trait RequestLoggable
{
  /**
   * Boot the RequestLoggable trait for a model.
   * @return void
   */
  public static function bootRequestLoggable()
  {
    static::extend(function (Model $model) {
      /**
       * @var array Attributes to be cast to JSON
       */
      $model->addJsonable(['requests', 'responses']);
    });
  }

  public function getSoftRequestsAttribute()
  {
    $new_requests = [];
    if (empty($this->requests)) {
      return $new_requests;
    }
    foreach ($this->requests as $request) {
      if (isset($request['request'])) {
        $new_requests[] = [
          'datetime' => $request['datetime'],
          'request' => $request['request'],
        ];
      }
    }
    krsort($new_requests);
    return $new_requests;
  }

  public function getSoftRequestsXMLAttribute()
  {
    $new_requests = [];
    if (empty($this->requests)) {
      return $new_requests;
    }
    foreach ($this->requests as $request) {
      if (isset($request['request'])) {
        $dom = new \DOMDocument();

// Initial block (must before load xml string)
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
// End initial block

        $dom->loadXML($request['request']);
        $new_requests[] = [
          'datetime' => $request['datetime'],
          'request' => $dom->saveXML(),
        ];
      }
    }
    krsort($new_requests);
    return $new_requests;
  }

  public function getSoftRequestsJSONAttribute()
  {
    $new_requests = [];
    if (empty($this->requests)) {
      return $new_requests;
    }
    foreach ($this->requests as $request) {
      if (isset($request['request'])) {
        $new_requests[] = [
          'datetime' => $request['datetime'],
          'request' => json_encode($request['request'], JSON_PRETTY_PRINT),
        ];
      }
    }
    krsort($new_requests);
    return $new_requests;
  }

  public function getSoftResponsesAttribute()
  {
    $new_responses = [];
    if (empty($this->responses)) {
      return $new_responses;
    }
    foreach ($this->responses as $response) {
      if (isset($response['response'])) {
        $new_responses[] = [
          'datetime' => $response['datetime'],
          'response' => $response['response'],
        ];
      }
    }
    krsort($new_responses);
    return $new_responses;
  }

  public function getSoftResponsesXMLAttribute()
  {
    $new_responses = [];
    if (empty($this->responses)) {
      return $new_responses;
    }
    foreach ($this->responses as $response) {
      if (isset($response['response'])) {
        $dom = new \DOMDocument();

// Initial block (must before load xml string)
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
// End initial block

        $dom->loadXML($response['response']);
        $new_responses[] = [
          'datetime' => $response['datetime'],
          'response' => $dom->saveXML(),
        ];
      }
    }
    krsort($new_responses);
    return $new_responses;
  }

  public function getSoftResponsesJSONAttribute()
  {
    $new_responses = [];
    if (empty($this->responses)) {
      return $new_responses;
    }
    foreach ($this->responses as $response) {
      if (isset($response['response'])) {
        $new_responses[] = [
          'datetime' => $response['datetime'],
          'response' => json_encode($response['response'], JSON_PRETTY_PRINT),
        ];
      }
    }
    krsort($new_responses);
    return $new_responses;
  }
}
