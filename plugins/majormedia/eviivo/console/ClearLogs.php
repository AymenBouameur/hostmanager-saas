<?php
namespace Majormedia\Eviivo\Console;

use Illuminate\Console\Command;
use File;
/**
 * ClearLogs Command
 *
 * @link https://docs.octobercms.com/3.x/extend/console-commands.html
 */
class ClearLogs extends Command
{
    protected $signature = 'logs:clear';
    protected $description = 'Clear all log files from storage/logs';

    public function handle()
    {
        $logPath = storage_path('logs');

        $files = File::glob($logPath . '/*.log');

        foreach ($files as $file) {
            file_put_contents($file, '');
        }

        $this->info('✅ Logs have been cleared!');
    }
}
