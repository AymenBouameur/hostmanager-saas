<?php
namespace MajorMedia\Eviivo;

use Majormedia\Eviivo\Console\ClearLogs;
use Majormedia\Eviivo\Console\FetchEviivoBookingsUpdated;
use System\Classes\PluginBase;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Scheduling\Schedule;
use Majormedia\Eviivo\Console\FetchEviivoBookings;
use Majormedia\Eviivo\Console\FetchPropertyContact;
use Majormedia\Eviivo\Console\FetchEviivoProperties;
use Majormedia\Eviivo\Console\ProcessEviivoPropertyBookings;


/**
 * Plugin class
 */
class Plugin extends PluginBase
{
    /**
     * register method, called when the plugin is first registered.
     */
    public function register()
    {
        $this->registerConsoleCommand('fetch:Eviivo-properties', FetchEviivoProperties::class);
        $this->registerConsoleCommand('fetch:Eviivo-bookings', FetchEviivoBookings::class);
        // $this->registerConsoleCommand('fetch:Eviivo-bookings-v2', FetchEviivoBookingsUpdated::class);
        $this->registerConsoleCommand('eviivo:sync-bookings', FetchEviivoBookingsUpdated::class);

        // $this->registerConsoleCommand('fetch:Eviivo-properties-contact', FetchPropertyContact::class);
        $this->registerConsoleCommand('logs:clear', ClearLogs::class);
    }

    /**
     * boot method, called right before the request route.
     */
    public function boot()
    {
    }

    /**
     * registerComponents used by the frontend.
     */
    public function registerComponents()
    {
    }

    /**
     * registerSettings used by the backend.
     */
    public function registerSettings()
    {
    }

    public function registerSchedule($schedule)
    {
        $schedule->command('fetch:eviivo-properties')
            ->dailyAt('12:00')
            ->appendOutputTo(storage_path('logs/fetch-eviivo-properties.log'))
            ->onFailure(fn() => Log::error('Fetch eviivo properties failed.'));

        $schedule->command('fetch:eviivo-bookings')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/fetch-eviivo-bookings.log'));
            
        $schedule->command('fetch:eviivo-bookings')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/fetch-eviivo-bookings.log'));

        $schedule->command('logs:clear')->weekly();
    }
}
