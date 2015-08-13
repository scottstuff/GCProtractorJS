<?php

namespace GotChosen\Util;

/**
 * Wrote this in 5 minutes while thinking about something. Probably will go unused as we'll likely
 * need a more robust system.
 *
 * Class BatchSendMailer
 *
 * $mailer = new BatchSendMailer($this->get('mailer'), $this->get('swiftmailer.transport.real'), 3000)
 * $mailer->send(...)
 *
 * @package GotChosen\Util
 */
class BatchSendMailer
{
    private $mailer;
    private $transport;
    private $batchCount;
    private $counter = 0;

    public function __construct(\Swift_Mailer $mailer, \Swift_Transport $transport, $batchCount)
    {
        $this->mailer = $mailer;
        $this->batchCount = $batchCount;
        $this->transport = $transport;
    }

    public function send(\Swift_Message $message)
    {
        $this->mailer->send($message);
        $this->counter++;
        if ( $this->counter >= $this->batchCount ) {
            $this->flush();
            $this->counter = 0;
        }
    }

    public function flush()
    {
        /** @var \Swift_Spool $spool */
        $spool = $this->mailer->getTransport()->getSpool();
        $spool->flushQueue($this->transport);
    }
}
