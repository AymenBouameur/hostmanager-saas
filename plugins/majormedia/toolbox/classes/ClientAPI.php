<?php namespace MajorMedia\ToolBox\Classes;

use Exception;
use FtpClient\FtpClient;
use GuzzleHttp\Client;
use MajorMedia\ToolBox\Traits\Notify;
use MajorMedia\TecDoc\Classes\Exceptions\ImportException;
use October\Rain\Exception\ApplicationException;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use SoapClient;
use ZipArchive;

ini_set('memory_limit', -1);
ini_set('default_socket_timeout', -1);

abstract class ClientAPI
{
  use Notify;

  protected const PROVIDER_REQUIRE_MODEL = false;
  protected const PROVIDER_MODEL_CLASS_NAMESPACE = '';
  protected const PROVIDER_MODE = 'stage';
  protected const PROVIDER_TYPE = '';
  protected const PROVIDER_URL = '';
  protected const PROVIDER_VERSION = '';
  protected const PROVIDER_USER_ID = '';
  protected const PROVIDER_USER_PASSWORD = '';
  protected const PROVIDER_API_KEY = '';
  protected const PROVIDER_USER_ID_STAGE = '';
  protected const PROVIDER_USER_PASSWORD_STAGE = '';
  protected const PROVIDER_API_KEY_STAGE = '';

  const SOAP_TRACE = true;
  const CLIENT_TYPES = [
    'SDK',
    'SOAP_1_1',
    'REST',
    'FORM',
    'FTP',
  ];

  protected $clientType = null;
  protected $enabled = true; // must be true !
  protected $apiClassName;
  protected $apiName;
  protected $apiBaseUrl;
  protected $apiVersion;
  protected $apiKey;
  protected $apiUserID;
  protected $apiUserPassword;
  protected array $sensitiveData = [];

  /*
   * For FtpClient
   */
  protected $ftpFilename = '';
  protected $ftpCsvSeparator = ';';
  protected $ftp_content = [];
  protected $ftpIgnoreFirstLine = false;

  protected $providerModel = null;
  protected $clientAPI = null;
  protected $parameters = [];
  protected $headers = [];
  protected $body = [];

  protected $errors = [];
  protected $logs = [];

  public function __construct()
  {
    $this->setDebugMode();
    $this->clientType = static::PROVIDER_TYPE;
    $this->apiClassName = class_basename($this);
    $this->apiName = strtoupper($this->apiClassName);
    $this->apiBaseUrl = static::PROVIDER_URL;
    $this->apiVersion = static::PROVIDER_VERSION;
    $this->apiUserID = static::PROVIDER_MODE == 'stage' ? static::PROVIDER_USER_ID_STAGE : static::PROVIDER_USER_ID;
    $this->apiUserPassword = static::PROVIDER_MODE == 'stage' ? static::PROVIDER_USER_PASSWORD_STAGE : static::PROVIDER_USER_PASSWORD;
    $this->apiKey = static::PROVIDER_MODE == 'stage' ? static::PROVIDER_API_KEY_STAGE : static::PROVIDER_API_KEY;

    if (!in_array($this->clientType, static::CLIENT_TYPES)) {
      throw new ApplicationException("The api Type must be in (" . implode(', ', static::CLIENT_TYPES) . "), '$this->clientType' given !");
    }

    if (empty($this->apiBaseUrl) && $this->clientType != 'SDK') {
      throw new ApplicationException("The api URL is missing for [$this->apiClassName] !");
    }

    // Position of this call is important !
    if (static::PROVIDER_REQUIRE_MODEL === true) {
      if (empty(static::PROVIDER_MODEL_CLASS_NAMESPACE)) {
        throw new ApplicationException("The 'PROVIDER_MODEL_CLASS_NAMESPACE' can not be empty if 'PROVIDER_REQUIRE_MODEL' is true for [$this->apiName] !");
      }
      $this->setProviderModel();
      if (empty($this->providerModel)) {
        throw new ApplicationException("The providerModel can not be empty for [$this->apiName] !");
      }
    }

    if (!$this->enabled) {
      throw new ApplicationException("The API [$this->apiName] is disabed !");
    }
  }

  public function pushParam($key, $value = null, $flush = false)
  {
    if (!is_array($key)) {
      $key = [$key => $value];
    }
    $this->parameters = $flush ? $key : array_merge($key, $this->parameters);
  }

  public function flushAllParams()
  {
    $this->pushParam([], null, true);
    return $this;
  }

  public function pushHeader($key, $value = null)
  {
    if (!is_array($key)) {
      $key = [$key => $value];
    }
    $this->headers = array_merge($this->headers, $key);
  }

  /*
   * For FtpCLient
   */

  public function getProviderModel()
  {
    return $this->providerModel;
  }

  protected function setProviderModel()
  {
    if ($this->providerModel = (static::PROVIDER_MODEL_CLASS_NAMESPACE)::whereProviderClass($this->apiClassName)->first()) {
      $this->enabled = $this->providerModel->is_active;
    }
  }

  public function getClientType()
  {
    return $this->clientType;
  }

  public function getClientAPI()
  {
    return $this->clientAPI;
  }

  public function getProviderId()
  {
    return $this->providerModel ? $this->providerModel->id : null;
  }

  public function getProviderName()
  {
    return $this->providerModel ? $this->providerModel->name : null;
  }

  public function getErrors()
  {
    return $this->errors;
  }

  public function getLogs($only = '*')
  {
    array_walk_recursive($this->logs, function (&$item, $key) {
      if ($key && in_array($key, $this->sensitiveData)) {
        $item = '***HIDDEN***';
      }
    });
    if (in_array($only, ['request', 'response'])) {
      $logs = [];
      foreach ($this->logs as $log) {
        if (isset($log[$only])) {
          $logs[] = $log[$only];
        }
      }
      return $logs;
    }
    return $this->logs;
  }

  protected function isJSON($string): bool
  {
    return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE);
  }

  protected function setupClientAPI($endpoint = '', $force = false)
  {
    if (!empty($this->clientAPI) && $force == false) {
      return;
    }
    if ($this->clientType == 'SOAP_1_1') {
      $this->clientAPI = new SoapClient($this->apiBaseUrl . $this->buildAPIVersion() . ($endpoint ? '/' . $endpoint : ''), [
        'trace' => static::SOAP_TRACE,
        'soap_version' => $this->clientType
      ]);
    } elseif ($this->clientType == 'REST') {
      $this->clientAPI = new Client([
        'base_uri' => $this->apiBaseUrl,
        'timeout' => 60,
      ]);
    } elseif ($this->clientType == 'FTP') {
      $this->clientAPI = new FtpClient();
      $this->clientAPI->connect($this->apiBaseUrl);
      $this->clientAPI->login($this->apiUserID, $this->apiUserPassword);
      //var_dump($this->clientAPI->scanDir('.', true));exit;
    }
  }

  protected function buildAPIVersion()
  {
    return $this->apiVersion ? '/' . $this->apiVersion : '';
  }

  protected function getFTPContent(&$softClientAPI = null, $softFilename = '', $softFtpIgnoreFirstLine = false, $force = false)
  {
    if ($force == true) {
      $this->ftp_content = [];
    }
    if (empty($this->ftp_content)) {
      $clientAPI = $softClientAPI ?: $this->clientAPI;
      $ftpFilename = $softFilename ?: $this->ftpFilename;
      $ftpIgnoreFirstLine = $softFtpIgnoreFirstLine ?: $this->ftpIgnoreFirstLine;
      try {
        if (empty($items = $clientAPI->scanDir('.', true)) || !isset($items['file#' . $ftpFilename])) {
          throw new Exception("There is no file in ftp directory or file '$ftpFilename' was not found !", 404);
        }
        $ftp = $clientAPI->getWrapper();
        if (!file_exists($targetDirectory = storage_path('app/media/tmp') . '/')) {
          mkdir($targetDirectory, 0777, true);
        }
        if ($ftp->get($targetDirectory . $ftpFilename, $ftpFilename, FTP_BINARY)) {
          $files_to_unlink = [];
          $filename = $ftpFilename;
          if (preg_match('#\.gz$#', $ftpFilename)) {

            // Raising this value may increase performance
            $buffer_size = 4096; // read 4kb at a time
            $filename = str_replace('.gz', '.csv', $ftpFilename);

            // Open our files (in binary mode)
            $file = gzopen($targetDirectory . $ftpFilename, 'rb');
            $out_file = fopen($targetDirectory . $filename, 'wb');

            // Keep repeating until the end of the input file
            while (!gzeof($file)) {
              // Read buffer-size bytes
              // Both fwrite and gzread and binary-safe
              fwrite($out_file, gzread($file, $buffer_size));
            }

            // Files are done, close files
            fclose($out_file);
            gzclose($file);
          } elseif (preg_match('#\.ZIP$#', $ftpFilename)) {
            $zip = new ZipArchive();
            if ($zip->open($targetDirectory . $ftpFilename)) {
              $zip->extractTo($targetDirectory);
              $zip->close();
              $filename = str_replace('.ZIP', '.CSV', $ftpFilename);
            } else {
              throw new Exception("Couldn't Unzip the file", 404);
            }
          } elseif (preg_match('#\.xlsx$#', $ftpFilename)) {
            $reader = new Xlsx();
            $spreadsheet = $reader->load($targetDirectory . $ftpFilename);
            $this->ftp_content = $spreadsheet->getActiveSheet()->toArray();
            if ($ftpIgnoreFirstLine) {
              unset($this->ftp_content[0]);
            }
          }
          $files_to_unlink[] = $targetDirectory . $filename;
          if (empty($this->ftp_content)) {
            $this->setFtpContent($targetDirectory . $filename, $ftpIgnoreFirstLine);
          }
          foreach ($files_to_unlink as $item) {
            @unlink($item);
          }
        }
        //dd($this->ftp_content);
      } catch (Exception $ex) {
        throw new ImportException($ex->getMessage(), $ex->getCode());
      }
    }
    return $this->ftp_content;
  }

  private function setFtpContent($filepath, $ftpIgnoreFirstLine)
  {
    $result = file_get_contents($filepath);
    $result = explode("\n", $result);
    if ($ftpIgnoreFirstLine) {
      unset($result[0]);
    }
    foreach ($result as $item) {
      $this->ftp_content[] = explode($this->ftpCsvSeparator, str_replace(["\r"], '', trim($item)));
    }
  }

}
