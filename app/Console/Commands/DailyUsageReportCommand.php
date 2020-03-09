<?php

namespace App\Console\Commands;

use App\Mail\DailyUsageReportMail;
use App\Session;
use App\Utils\Slack;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Console\Input\InputOption;

class DailyUsageReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:daily-usage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->addOption('date', null, InputOption::VALUE_REQUIRED);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = $this->option('date', date('Y-m-d'));
        $result = [];
        $sessions = Session::whereBetween('start_at', [sprintf('%s 00:00:00', $date), sprintf('%s 23:59:59', $date)])->get();
        foreach ($sessions as $session) {
            if (!isset($result[$session->user_id])) {
                $result[$session->user_id] = [
                    'name' => $session->user_name,
                    'email' => $session->user_email,
                    'duration' => 0,
                    'sessions' => []
                ];
            }
            $duration = (int)round((strtotime($session->end_at) - strtotime($session->start_at)) / 60);
            $result[$session->user_id]['sessions'][] = [
                'start_at' => $session->start_at,
                'end_at' => $session->end_at,
                'duration' => $duration
            ];
            $result[$session->user_id]['duration'] += $duration;
        }
        $result = array_values($result);
        usort($result, function ($a, $b) {
            return $b['duration'] - $a['duration'];
        });

        Mail::to('sebastien@etincelle-coworking.com')->send(new DailyUsageReportMail($result, $date));
    }

    protected function durationToHuman($minutes)
    {
        $hours = floor(abs($minutes) / 60);
        $minutes = abs($minutes) % 60;
        $result = '';
        if ($minutes < 0) {
            $result .= '- ';
        }
        if ($hours) {
            $result = $hours . 'h';
        }

        if ($minutes) {
            if ($result) {
                $result .= ' ';
            }
            $result .= $minutes . 'min';
        }
        return $result;
    }


}
