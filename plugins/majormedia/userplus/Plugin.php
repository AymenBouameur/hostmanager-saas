<?php

namespace MajorMedia\UserPlus;

use Event;
use RainLab\User\Models\User;
use System\Classes\PluginBase;
use Backend\Classes\Controller;
use October\Rain\Database\Model;
use Majormedia\UserPlus\Models\Role;
use MajorMedia\UserPlus\Traits\checkIfUserIsBanned;
use RainLab\User\Controllers\Users as UsersController;

/**
 * UserPlus Plugin Information File
 */
class Plugin extends PluginBase
{
    use checkIfUserIsBanned;

    public $require = ['RainLab.User'];

    public function registerSettings()
    {
        return [];
    }

    public function boot()
    {
        $this->extendUserModel();
        $this->extendUsersController();
        $this->extendRainlabUserNavigation();

        Event::listen('majormedia.userplus::user.logged', function (User $user) {
            $this->checkIfUserIsBanned($user);
        });
        Event::listen('backend.menu.extendItems', function ($manager) {
            $manager->removeMainMenuItem('RainLab.Builder', 'builder');
            $manager->removeMainMenuItem('October.Editor', 'editor');
            // $manager->removeMainMenuItem('October.Media', 'media');
        });

    }


    protected function extendUserModel()
    {
        User::extend(function (Model $model) {
            // $model->rules = [];
            $model->append(['avatar_path']);
            $model->attachOne['featured_images'] = ['System\Models\File'];

            $model->addVisible([
                'name',
                'surname',
                'address',
                'email',
                'avatar_path',
                'phone',
            ]);
            $model->addFillable([
                'name',
                'surname',
                'phone',
                'description',
            ]);
            $model->addDynamicMethod('getAvatarPathAttribute', function () use ($model) {
                $defaultImagePath = url('/plugins/majormedia/userplus/assets/images/default-user-img.png');
                return $model->avatar ? $model->avatar->getPath() : $defaultImagePath;
            });

            $model->bindEvent('model.afterUpdate', function () use ($model) {
                $originalPassword = $model->getOriginal('password');
                $newPassword = $model->password;

                if (!\Hash::check($newPassword, $originalPassword)) {
                    \Mail::sendTo($model->email, 'majormedia.userplus::mail.password_changed', [
                        'user' => $model,
                    ]);
                }
            });


        });
    }
    protected function extendUsersController()
    {
        UsersController::extend(function (Controller $controller) {
            // Config_list
            if (!isset($controller->listConfig)) {
                $controller->implement[] = 'Backend.Behaviors.ListController';
                $controller->addDynamicProperty('listConfig');
            }
            $controller->listConfig = $controller->mergeConfig(
                $controller->listConfig,
                '$/majormedia/userplus/config/user_config_list.yaml'
            );

            // Config_form
            if (!isset($controller->formConfig)) {
                $controller->implement[] = 'Backend.Behaviors.FormController';
                $controller->addDynamicProperty('formConfig');
            }
            $controller->formConfig = $controller->mergeConfig(
                $controller->formConfig,
                '$/majormedia/userplus/config/user_config_form.yaml'
            );

            // Config_relation
            if (!isset($controller->relationConfig)) {
                $controller->implement[] = 'Backend.Behaviors.RelationController';
                $controller->addDynamicProperty('relationConfig');
            }
            $controller->relationConfig = $controller->mergeConfig(
                $controller->relationConfig,
                '$/majormedia/userplus/config/user_config_relation.yaml'
            );
        });
    }

    protected function extendRainlabUserNavigation()
    {
        \Event::listen('backend.menu.extendItems', function ($manager) {
            // remove user groups side menu
            $manager->removeSideMenuItem('RainLab.User', 'user', 'usergroups');
        });
    }
}
