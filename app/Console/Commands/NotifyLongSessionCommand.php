<?php

namespace App\Console\Commands;

use App\Session;
use App\Utils\Slack;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class NotifyLongSessionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:long-sessions';

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
        $this->addOption('delay', null, InputOption::VALUE_REQUIRED);
        $this->addOption('frequency', null, InputOption::VALUE_REQUIRED);
        $this->addOption('dry-run', null, InputOption::VALUE_NONE);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$this->createTwilioClient()) {
            return false;
        }

        $notification_delay = (int)$this->option('delay');
        if (empty($notification_delay)) {
            $notification_delay = 50;
        }
        $notification_frequency = (int)$this->option('frequency');
        if (empty($notification_frequency)) {
            $notification_frequency = 5;
        }

        $this->getOutput()->writeln(sprintf('Delay : %d, Frequency : %d', $notification_delay, $notification_frequency));
        $sessions = Session::whereNull('end_at')
            ->where(function ($query) use ($notification_delay, $notification_frequency) {
                $query->where(function ($query) use ($notification_delay) {
                    $query->whereNull('last_notification_at')
                        ->where('start_at', '<', date('Y-m-d H:i:s', strtotime(sprintf('-%d minutes', $notification_delay))));
                })
                    ->orWhere('last_notification_at', '<', date('Y-m-d H:i:s', strtotime(sprintf('-%d minutes', $notification_frequency))));
            })->get();

        $count = count($sessions);
        $this->getOutput()->writeln(sprintf('Found %d session%s', $count, ($count > 1 ? 's' : '')));
        $this->getOutput()->writeln('');
        if ($count) {
            $index = 1;
            foreach ($sessions as $session) {
                $this->getOutput()->writeln(sprintf('%d/%d Session started at %s by %s', $index++, $count, date('d/m/Y H:i:s', strtotime($session->start_at)), $session->user_name));

                if ($notification_uuid = $this->sendNotification($session)) {
                    if ($this->option('dry-run')) {
                        $this->getOutput()->writeln(sprintf(' -- Notification muted'));
                    } else {
                        $this->getOutput()->writeln(sprintf(' -- Notified (UUID : %s)', $notification_uuid));
                        $session->last_notification_at = date('Y-m-d H:i:s');
                        $session->last_notification_uuid = $notification_uuid;
                        $session->save();
                    }
                } else {
                    $this->getOutput()->writeln(sprintf(' -- Notified failed (Phone : %s)', $session->user_phone));
                }
            }
        }
    }

    protected $twilio;

    protected function createTwilioClient()
    {
        $twilio_number = env('TWILIO_SMS_NUMBER');
        $account_sid = env('TWILIO_ACCOUNT_SID');
        $auth_token = env('TWILIO_AUTH_TOKEN');
        if (empty($twilio_number) || empty($account_sid) || empty($auth_token)) {
            $this->getOutput()->writeln('<error>Twilio settings are not set</error>');
            return false;
        }

        $this->twilio = new \Twilio\Rest\Client($account_sid, $auth_token);
        return true;
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

    protected function sendNotification($session)
    {
        if (empty($session->user_phone)) {
            return false;
        }
        $duration = $this->durationToHuman((time() - strtotime($session->start_at)) / 60);
        $this->getOutput()->writeln(sprintf('Start = %s, Now = %s, duration = %s', $session->start_at, date('Y-m-d H:i:s', time()), $duration));
        $message_content = sprintf('@Etincelle - Box utilisé depuis %s, penses à le libérer pour les autres coworkers - Merci',
            $duration);

        $this->getOutput()->writeln($message_content);

        Slack::postMessage(env('SLACK_HOOK'), array(
            'text' => sprintf('SMS envoyé à %s au %s', $session->user_name, $session->user_phone),
            'attachments' => array(
                array(
                    "text" => $message_content
                )),
        ));

        if ($this->option('dry-run')) {
            return true;
        }

        $result = $this->twilio->messages->create(
            $session->user_phone,
            array(
                'from' => env('TWILIO_SMS_NUMBER'),
                'body' => $message_content
            )
        );
        return $result->sid;
    }

}
