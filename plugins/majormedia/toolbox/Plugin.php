<?php
namespace MajorMedia\ToolBox;

use App;
use Config;
use DB;
use Event;
use Mail;
use MajorMedia\ToolBox\Components\Cities;
use MajorMedia\ToolBox\Components\Countries;
use MajorMedia\ToolBox\Components\HelpWidget;
use MajorMedia\ToolBox\Components\Newsletters;
use MajorMedia\ToolBox\Components\ReCaptcha;
use MajorMedia\ToolBox\Components\Tools;
use MajorMedia\ToolBox\Exceptions\RESTErrorException;
use MajorMedia\ToolBox\Models\Settings;
use Illuminate\Foundation\AliasLoader;
use MajorMedia\ToolBox\Traits\FormatConverter;
use MajorMedia\ToolBox\Traits\SlackNotify;
use October\Rain\Database\Model;
use October\Rain\Exception\ApplicationException;
use RainLab\User\Models\User;
use RainLab\User\Controllers\Users as UsersController;
use System\Classes\PluginBase;
use View;

class Plugin extends PluginBase
{
  use FormatConverter;
  use SlackNotify;

  public $elevated = true;
  public $require = [
    'RainLab.User',
    'Responsiv.Uploader',
    'October.Drivers'
  ];

  public function registerComponents()
  {
    return [
      Tools::class => 'tools',
      Cities::class => 'citiesDP',
      Countries::class => 'countriesDP',
      Newsletters::class => 'newsletter',
      ReCaptcha::class => 'reCaptcha',
      HelpWidget::class => 'helpWidget',
    ];
  }

  public function registerSettings()
  {
    return [
      'settings' => [
        'label' => 'Tools',
        'description' => 'All settings needed for One Page.',
        'icon' => 'icon-cogs',
        'class' => Models\Settings::class,
        'order' => 500,
        'keywords' => ''
      ]
    ];
  }

  public function register()
  {
    $this->registerConsoleCommand('create.restcontroller', 'MajorMedia\ToolBox\Console\CreateRestController');
    $this->registerConsoleCommand('toolbox.errorCode', 'MajorMedia\ToolBox\Console\CreateErrorCode');
    $this->registerConsoleCommand('key.regenerate', 'MajorMedia\ToolBox\Console\KeyRegenerateCommand');

    // Instantiate the AliasLoader
    $aliasLoader = AliasLoader::getInstance();

    // Register aliases
    $aliasLoader->alias('ErrorCodes', \Majormedia\ToolBox\Utility\ErrorCodes::class);
  }


  public function boot()
  {
    $this->initRecaptcha();

    $this->extendUserModel();
    $this->extendUserFormFields();
    $this->extendUsersRelationConfig();

    View::share('app_url', env('APP_URL'));
    // todo: Solution provisoire
    if (DB::connection()->getDatabaseName() != 'database' && \Schema::hasTable('system_settings')) {
      View::share('app_name', Settings::get('contact_name'));
      View::share('app_contact_email', Settings::get('contact_email'));
      View::share('app_contact_phone', Settings::get('contact_phone'));
      View::share('app_contact_fix', Settings::get('contact_fix'));
      View::share('app_contact_name', Settings::get('contact_name'));
      View::share('app_address', Settings::get('address'));
    }

    Event::listen('majormedia.toolbox::new_message_from_admin', function ($message) {
      $users = User::all();
      $topic = $message->subject;
      $body = $message->message;
      \Log::info('Admin Announcement sent to all users begin');
      foreach ($users as $user) {
        Mail::send('majormedia.toolbox::mail.new_message_from_admin', [
          'user' => $user,
          'body' => $body,
          'topic' => $topic,
        ], function ($message) use ($user, $topic) {
          $message->to($user->email);
          $message->subject($topic);
        });
      }
      \Log::info('Admin Announcement sent to all users ends');
    });

    Event::listen('majormedia.toolbox::event.new_message', function ($message) {

      // Envoyer un mail aux administrateurs du site
      $contacts = Settings::get('form_contact_email') ? [Settings::get('form_contact_email') => Settings::get('form_contact_name')] : [];
      if (Settings::get('form_contact_notif_all_admins', false)) {
        $admins = \Backend\Models\User::all();
        foreach ($admins as $admin) {
          if (!empty($admin->email))
            $contacts[$admin->email] = $admin->full_name;
        }
      }

      foreach ($contacts as $email => $name) {
        Mail::send('majormedia.toolbox::mail.admin_new_message', ['form' => $message], function ($message) use ($email, $name) {
          $message->to($email, $name);
        });
      }

    });

    App::error(function (RESTErrorException $ex) {
      return response()->json(['code' => $ex->getStatusCode(), 'message' => $ex->getMessage()], $ex->getCode());
    });

    App::error(function (ApplicationException $ex) {
      $this->pushMessage(str_limit($ex->getMessage(), 250, '...'), 'debug');
      $this->notifySlack(false);
    });

    /*\System\Controllers\Settings::extend(function ($controller) {
      $controller->addDynamicMethod('onDeploy', function () {
        try {
          //$output = new BufferedOutput();

          $process = Ssh\Ssh::create('autodoc', 'mididis.com')->execute('ls -ail');

          //\Artisan::call('tecdoc:import', ['type' => 'product', '--brand_ei' => null, '--product_ei' => null], $output);
        } catch (Exception $e) {
          Response::make($e->getMessage(), 500);
        }
        return ['#artisan_output' => str_replace("\n", '<br>', $process->getOutput())];
      });
    });*/

    /*
     * todo: Register Wizard Singleton
     */
    /*App::singleton('Wizard', function() {
      return new WizardManager();
    });
    App::register(WizardManagerServiceProvider::class);
    AliasLoader::getInstance()->alias('Wizard', Facades\Wizard::class);*/
  }

  private function initRecaptcha()
  {
    /*CmsController::extend(function ($controller) {
      $controller->middleware('MajorMedia\ToolBox\Middleware\ReCaptchaMiddleware');
    });*/
  }

  protected function extendUserModel()
  {
    if (class_exists('RainLab\User\Models\User')) {
      User::extend(function (Model $model) {

        $model->hasMany['messages'] = [\Majormedia\Toolbox\Models\Message::class];

        $model->addDynamicMethod('getFullNameAttribute', function () use ($model) {
          return ucfirst(strtolower($model->name)) . ' ' . strtoupper($model->surname);
        });

        $model->addDynamicMethod('getSoftNameAttribute', function () use ($model) {
          return !empty($model->surname) ? strtoupper($model->surname[0]) . '. ' . ucfirst(strtolower($model->name)) : 'Utilisateur';
        });

        $model->addDynamicMethod('getBackendUserPreviewAttribute', function () use ($model) {
          return url('/' . (Config::get('cms.backendUri', 'backend')) . '/rainlab/user/users/preview/' . $model->id);
        });
      });
    }
  }

  public function registerMailTemplates()
  {
    return [
      'majormedia.toolbox::mail.admin_new_message',
    ];
  }

  public function registerMarkupTags()
  {
    return [
      'filters' => [
        'unitconvert' => [$this, 'unit'],
        'price' => [$this, 'price'],
      ],
    ];
  }

  private function extendUsersRelationConfig()
  {
    \Rainlab\User\Controllers\Users::extend(function (\Backend\Classes\Controller $controller) {
      // Config_relation
      if (!in_array('Backend\Behaviors\RelationController', $controller->implement) && !in_array('Backend.Behaviors.RelationController', $controller->implement)) {
        $controller->implement[] = 'Backend\Behaviors\RelationController';
      }
      if (!isset($controller->relationConfig)) {
        $controller->addDynamicProperty('relationConfig');
      }

      $controller->relationConfig = $controller->mergeConfig(
        $controller->relationConfig,
        __DIR__ . '/config/user_config_relation.yaml'
      );
    });
  }

  private function extendUserFormFields()
  {
    Event::listen('backend.form.extendFields', function ($widget) {
      // Only for the Users controller
      if (!$widget->getController() instanceof UsersController) {
        return;
      }

      // Only for the User model
      if (!$widget->model instanceof User) {
        return;
      }

      $widget->addTabFields([
        'messages' => [
          'label' => 'Messages',
          'tab' => 'Messages',
          'type' => 'partial',
          'path' => '$/majormedia/toolbox/controllers/messages/_messages.htm',
        ],
      ]);
    });
  }

}
