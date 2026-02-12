<?php
namespace MajorMedia\ToolBox\Behaviors;

use Db;
use Str;
use Lang;
use Request;
use Exception;
use ValidationException;
use Backend\Classes\ControllerBehavior;
use October\Rain\Database\ModelException;

/**
 * Rest Controller Behavior
 *
 * Adds REST features for working with backend models.
 *
 * Usage:
 *
 * In the model class definition:
 *
 *   public $implement = ['MajorMedia.ToolBox.Behaviors.RestController'];
 *
 * @author Saifur Rahman MajorMedia
 */
class RestController extends ControllerBehavior
{
  use \Backend\Traits\FormModelSaver;
  use \Majormedia\ToolBox\Traits\GetCurrentUserFromJWT;

  /**
   * @var Model The child controller that implements the behavior.
   */
  protected $controller;

  /**
   * @var Model The initialized model used by the rest controller.
   */
  protected $model;

  /**
   * @var String The prefix for verb methods.
   */
  protected $prefix = '';

  /**
   * {@inheritDoc}
   */
  protected $requiredProperties = ['restConfig'];

  /**
   * @var array Configuration values that must exist when applying the primary config file.
   * - modelClass: Class name for the model
   * - list: List column definitions
   */
  protected $requiredConfig = ['modelClass', 'allowedActions'];

  /**
   * Behavior constructor
   * @param Backend\Classes\Controller $controller
   */
  public function __construct($controller)
  {
    parent::__construct($controller);
    $this->controller = $controller;

    /*
     * Build configuration
     */
    $this->config = $this->makeConfig($controller->restConfig, $this->requiredConfig);
    $this->config->modelClass = Str::normalizeClassName($this->config->modelClass);

    if (isset($this->config->prefix)) {
      $this->prefix = $this->config->prefix;
    }
  }

  /**
   * Creates a new instance of the model. This logic can be changed
   * by overriding it in the rest controller.
   * @return Model
   */
  public function createModelObject()
  {
    return $this->createModel();
  }

  /**
   * Display the records.
   *
   * @return Response
   */
  public function index()
  {
    if (!array_key_exists('index', $this->config->allowedActions)) {
      return response()->json("The action [index] is not authorized !", 403);
    }

    $options = is_array($this->config->allowedActions['index']) ? $this->config->allowedActions['index'] : [];
    $relations = isset($this->config->allowedActions['index']['relations']) ? $this->config->allowedActions['index']['relations'] : [];
    $page = Request::input('page', 1);
    $pageSize = Request::input('pageSize', 15);

    /*
     * Default options
     */
    extract(array_merge($options, [
      'page' => $page,
      'pageSize' => $pageSize
    ]));

    try {


      $model = $this->controller->createModelObject();
      $model = $this->controller->extendModel($model) ?: $model;

      if ($model instanceof \Illuminate\Http\JsonResponse) {
        return $model;
      }

      // Check if pageSize is set to -1, in which case fetch all data without pagination
      if ($pageSize == -1) {
        $data = $model->with($relations)->get();
        $response = [
          'status' => 'success',
          'data' => $this->controller->extendAfterFetch($data)->toArray()
        ];
        return $response;
      } else {
        $response = $this->controller->getPaginator($model->with($relations), $pageSize, $page);
        $response = array_merge(
          ['status' => 'success'],
          $this->controller->extendAfterFetch($response)->toArray()
        );
        if (isset($response['refreshPaginator'])) {
          $newPage = isset($response['newCurrentPage']) ? $response['newCurrentPage'] : $page;
          $newPageSize = isset($response['newPageSize']) ? $response['newPageSize'] : $pageSize;
          $response = $this->controller->getPaginator($model->with($relations), $newPageSize, $newPage);
        }
      }

      return response()->json($response, 200);
    } catch (Exception $ex) {
      return response()->json($ex->getMessage(), 400);
    }
  }

  /**
   * Store a newly created record using post data.
   *
   * @return Response
   */
  public function store()
  {
    if (!array_key_exists('store', $this->config->allowedActions)) {
      return response()->json("The action [store] is not authorized !", 403);
    }

    $data = Request::all();

    try {
      $model = $this->controller->createModelObject();

      $modelsToSave = $this->prepareModelsToSave($model, $data);
      foreach ($modelsToSave as $modelToSave) {
        $this->controller->extendModelBeforeSave($modelToSave);
        $modelToSave->save();
      }

      return response()->json($model, 200);
    } catch (ModelException $ex) {
      return response()->json($ex->getMessage(), 400);
    } catch (Exception $ex) {
      return response()->json($ex->getMessage(), 400);
    }
  }
  /**
   * Display the specified record.
   *
   * @param int $recordId
   * @return Response
   */
  public function show($recordId)
  {
    if (!array_key_exists('show', $this->config->allowedActions)) {
      return response()->json("The action [show] is not authorized !", 403);
    }

    if (!is_numeric($recordId)) {
      return response()->json("The field [recordId] must be a numeric, '$recordId' given !", 400);
    }

    $relations = isset($this->config->allowedActions['show']['relations']) ? $this->config->allowedActions['show']['relations'] : [];
    try {
      $model = $this->controller->findModelObject($recordId, $relations);
      $model = $this->controller->extendAfterFetch($model);
      return response()->json([
        'status' => 'success',
        'data' => $model,
      ], 200);
    } catch (ModelException $ex) {
      return response()->json($ex->getMessage(), 400);
    } catch (Exception $ex) {
      return response()->json($ex->getMessage(), 400);
    }
  }

  /**
   * Update the specified record in using post data.
   *
   * @param int $recordId
   * @return Response
   */
  public function update($recordId)
  {
    if (!array_key_exists('update', $this->config->allowedActions)) {
      return response()->json("The action [update] is not authorized !", 403);
    }

    if (!is_numeric($recordId)) {
      return response()->json("The field [recordId] must be a numeric, '$recordId' given !", 400);
    }

    $data = Request::all();

    try {
      $model = $this->controller->findModelObject($recordId);

      $modelsToSave = $this->prepareModelsToSave($model, $data);
      foreach ($modelsToSave as $modelToSave) {
        $modelToSave->save();
      }

      return response()->json($model, 200);
    } catch (ModelException $ex) {
      return response()->json($ex->getMessage(), 400);
    } catch (Exception $ex) {
      return response()->json($ex->getMessage(), 400);
    }
  }

  /**
   * Remove the specified record.
   *
   * @param int $id
   * @return Response
   */
  public function destroy($recordId)
  {
    if (!array_key_exists('destroy', $this->config->allowedActions)) {
      return response()->json("The action [destroy] is not authorized !", 403);
    }

    if (!is_numeric($recordId)) {
      return response()->json("The field [recordId] must be a numeric, '$recordId' given !", 400);
    }

    try {
      $model = $this->controller->findModelObject($recordId);
      $model->delete();
      return response()->json($model, 200);
    } catch (ModelException $ex) {
      return response()->json($ex->getMessage(), 400);
    } catch (Exception $ex) {
      return response()->json($ex->getMessage(), 400);
    }
  }

  /**
   * Finds a Model record by its primary identifier, used by show, update actions.
   * This logic can be changed by overriding it in the rest controller.
   * @param string $recordId
   * @return Model
   */
  public function findModelObject($recordId, array $relations = [])
  {
    if (!strlen($recordId)) {
      throw new Exception('Record ID has not been specified.');
    }

    $model = $this->controller->createModelObject();

    /*
     * Prepare query and find model record
     */
    $query = $model->newQuery();
    $result = $query->whereId($recordId);

    if (!$result) {
      throw new Exception(sprintf('Record with an ID of %u could not be found.', $recordId));
    }
    if (!empty($relations)) {
      $result->with(...$relations);
    }

    $result = $this->controller->extendModel($result, $recordId) ?: $result;

    return $result->first();
  }

  /**
   * Internal method, prepare the model object
   * @return Model
   */
  protected function createModel()
  {
    $class = $this->config->modelClass;
    return new $class();
  }

  /**
   * Extend supplied model, the model can
   * be altered by overriding it in the controller.
   * @param Model $model
   * @return Model
   */
  public function extendModel($model, $recordId = null)
  {
    return $model;
  }

  public function extendModelBeforeSave(&$model)
  {
  }

  public function extendAfterFetch($response)
  {
    return $response;
  }

  public function getPaginator($model, $pageSize, $page)
  {
    return $model->paginate($pageSize, $page);
  }
}
