<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected string $disruptedEmailAddress;
    protected string $name;
    public function __construct( $disruptedEmailAddress, $name )
    {
        $this->disruptedEmailAddress = $disruptedEmailAddress;
        $this->name = $name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.confirmed')
        ->subject('SHAKESBIERRE - Reservation Confirmation');
    }
}
