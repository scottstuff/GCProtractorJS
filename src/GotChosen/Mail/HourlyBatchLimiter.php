<?php

namespace GotChosen\Mail;

use GotChosen\SiteBundle\Entity\MassMailBatch;

class HourlyBatchLimiter extends AbstractBatchLimiter
{
    /**
     * {@inheritdoc}
     */
    public function getMessagesRemaining(\DateTime $currentDate)
    {
        $repo = $this->em->getRepository('GotChosenSiteBundle:MassMailBatch');

        /** @var MassMailBatch $entry */
        $entry = $repo->findOneBy([
            'day'  => $currentDate,
            'hour' => $currentDate->format('G'),
        ]);

        $total = 0;
        if ( $entry !== null ) {
            $total = $entry->getMessagesSent();
        }

        return max(0, $this->limit - $total);
    }
}
