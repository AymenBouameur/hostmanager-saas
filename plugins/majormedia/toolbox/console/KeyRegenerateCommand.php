<?php namespace MajorMedia\ToolBox\Console;

use Illuminate\Foundation\Console\KeyGenerateCommand as KeyGenerateCommandBase;

class KeyRegenerateCommand extends KeyGenerateCommandBase
{
  /**
   * @var string The console command name.
   */
  protected $signature = 'key:regenerate';

  /**
   * @var string The console command description.
   */
  protected $description = "Set the application key";

  /**
   * Execute the console command.
   *
   * @return void
   */
  public function handle()
  {
    $key = $this->generateRandomKey();

    // Next, we will replace the application key in the environment file so it is
    // automatically setup for this developer. This key gets generated using a
    // secure random byte generator and is later base64 encoded for storage.
    if (! $this->setKeyInEnvironmentFile($key)) {
      return;
    }

    $this->laravel['config']['app.key'] = $key;

    $this->info("Application key [$key] set successfully.");
  }
}
