<?php

namespace GotChosen\Mail;

use Doctrine\ORM\EntityManager;
use GotChosen\SiteBundle\Entity\MassMailBatch;

class DailyBatchLimiter extends AbstractBatchLimiter
{
    /**
     * {@inheritdoc}
     */
    public function getMessagesRemaining(\DateTime $currentDate)
    {
        $repo = $this->em->getRepository('GotChosenSiteBundle:MassMailBatch');
        $entries = $repo->findBy(['day' => $currentDate]);

        $total = 0;
        /** @var MassMailBatch $entry */
        foreach ( $entries as $entry ) {
            $total += $entry->getMessagesSent();
        }

        return max(0, $this->limit - $total);
    }
}
