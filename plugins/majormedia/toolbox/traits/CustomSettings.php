<?php namespace MajorMedia\ToolBox\Traits;

use Backend\Models\BrandSetting;
use Backend\Models\User;
use Backend\Models\UserRole;

trait CustomSettings
{
  public function initProject($path)
  {

    $dotenv = new \Dotenv\Dotenv($path, '.constants');
    $dotenv->safeLoad();

    // Set Brand Settings
    $this->setProjectSettings();

    // Set Brand Settings
    $this->setBrandSettings();

    // Set Backend additional user
    $this->setBackendManagerUser();

    // Set Backend user interface
    $this->setUserPreferences($this->getDefaultManagerId());

    // Set Mail default settings
    $this->setNewSystemSettings('system_mail_settings', '{"send_mode":"' . env('MAIL_DRIVER', 'log') . '","sender_name":"' . env('APP_NAME', '') . '","sender_email":"' . env('APP_EMAIL', '') . '","sendmail_path":"\/usr\/sbin\/sendmail -bs","smtp_address":"' . env('MAIL_HOST', 'smtp.mailgun.org') . '","smtp_port":"' . env('MAIL_PORT', '587') . '","smtp_user":"' . env('MAIL_USERNAME', '') . '","smtp_password":"' . env('MAIL_PASSWORD', '') . '","smtp_authorization":"1","smtp_encryption":"' . env('MAIL_ENCRYPTION', 'tls') . '","mailgun_domain":"","mailgun_secret":"","mandrill_secret":"","ses_key":"","ses_secret":"","ses_region":"","sparkpost_secret":""}');
  }

  private function setNewSystemSettings($item, $value)
  {
    $table = 'system_settings';
    $data = ['item' => $item, 'value' => $value];
    if ($id = \DB::table($table)->whereItem($item)->value('id')) {
      \DB::table($table)->whereId($id)->update($data);
    } else {
      \DB::table($table)->insert($data);
    }
  }

  private function setUserPreferences($user_id = 1)
  {
    $table = 'backend_user_preferences';
    $data = [
      'user_id' => $user_id,
      'namespace' => 'backend',
      'group' => 'backend',
      'item' => 'preferences',
      'value' => "{\"locale\":\"fr\",\"fallback_locale\":\"en\",\"timezone\":\"Africa\\/Casablanca\",\"editor_font_size\":\"12\",\"editor_word_wrap\":\"fluid\",\"editor_code_folding\":\"manual\",\"editor_tab_size\":\"4\",\"editor_theme\":\"twilight\",\"editor_show_invisibles\":\"0\",\"editor_highlight_active_line\":\"1\",\"editor_use_hard_tabs\":\"0\",\"editor_show_gutter\":\"1\",\"editor_auto_closing\":\"0\",\"editor_autocompletion\":\"manual\",\"editor_enable_snippets\":\"0\",\"editor_display_indent_guides\":\"0\",\"editor_show_print_margin\":\"0\",\"rounded_avatar\":\"1\",\"topmenu_label\":\"0\",\"sidebar_description\":\"0\",\"sidebar_search\":\"0\",\"focus_searchfield\":\"0\",\"context_menu\":\"0\",\"virtual_keyboard\":\"0\",\"user_id\":" . $user_id . "}",
    ];
    if ($id = \DB::table($table)->whereUserId($user_id)->value('id')) {
      \DB::table($table)->whereId($id)->update($data);
    } else {
      \DB::table($table)->insert($data);
    }
  }

  private function getRoleId()
  {
    return 3;
  }

  private function getDefaultManagerId()
  {
    return 2;
  }

  private function setManagerRole()
  {
    if (UserRole::find($this->getRoleId()))
      return true;
    $brole = new UserRole();
    $brole->id = $this->getRoleId();
    $brole->name = "Gestionnaire";
    $brole->code = 'manager';
    $brole->description = "Administrateur gestionnaire de l'application";
    $brole->permissions = [
      'backend.manage_preferences' => 1,
    ];
    $brole->save();
  }

  private function setBrandSettings()
  {
    $brand = BrandSetting::whereItem('backend_brand_settings')->first() ?: new BrandSetting;
    $brand->app_name = env('APP_NAME', '');
    $brand->app_tagline = env('APP_TAGLINE', '');
    $brand->primary_color = env('BACKEND_PRIMARY_COLOR', '');
    $brand->secondary_color = env('BACKEND_SECONDARY_COLOR', '');
    $brand->accent_color = env('BACKEND_ACCENT_COLOR', '');
    $brand->menu_mode = env('BACKEND_MENU_MODE', '');
    $brand->logo = plugins_path(env('BACKEND_LOGO_PATH', ''));
    $brand->favicon = plugins_path(env('BACKEND_FAVICON_PATH', ''));
    $brand->save();
  }

  private function setBackendManagerUser()
  {
    $buser = User::find($this->getDefaultManagerId()) ?: new User;
    $buser->id = $this->getDefaultManagerId();
    $buser->role = $this->getRoleId();
    $buser->first_name = env('BACKEND_USER_FIRSTNAME', '');
    $buser->last_name = env('BACKEND_USER_LASTNAME', '');
    $buser->login = env('BACKEND_USER_LOGIN', '');
    $buser->email = env('BACKEND_USER_EMAIL', '');
    $buser->password = env('BACKEND_USER_PASSWORD', '');
    $buser->password_confirmation = env('BACKEND_USER_PASSWORD', '');
    $buser->is_activated = 0;
    $buser->role_id = $this->getRoleId();
    $buser->is_superuser = 0;
    $buser->save();
  }

  private function setProjectSettings()
  {
    \Artisan::call('october:util', ['name' => 'set project', '--projectId' => env('OCMS_LICENCE_KEY', '')]);
    \Artisan::call('october:fresh', ['--force' => true]);
    \Artisan::call('key:regenerate');
    \Artisan::call('cache:clear');
  }
}