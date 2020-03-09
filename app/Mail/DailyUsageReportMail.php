<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DailyUsageReportMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $items;
    public $date;

    public function __construct($items, $date)
    {
        $this->items = $items;
        $this->date = $date;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('support@etincelle-coworking.com')
            ->subject(sprintf('PhoneBox Daily Report - %s', date('d/m/Y', strtotime($this->date))))
            ->view('mail.daily-usage-report');
    }
}
