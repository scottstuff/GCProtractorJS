<?php

namespace GotChosen\Mail;

interface BatchLimiterInterface
{
    /**
     * Returns the number of messages that can be sent in the given timeframe.
     *
     * @param \DateTime $currentDate
     * @return integer
     */
    public function getMessagesRemaining(\DateTime $currentDate);

    /**
     * Sets the message limit.
     *
     * @param $limit
     * @return void
     */
    public function setLimit($limit);

    /**
     * Adds to the number of messages sent in the given timeframe.
     *
     * @param \DateTime $currentDate
     * @param $count
     * @return void
     */
    public function addMessagesSent(\DateTime $currentDate, $count);
}
