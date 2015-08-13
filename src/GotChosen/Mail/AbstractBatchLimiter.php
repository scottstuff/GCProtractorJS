<?php

namespace GotChosen\Mail;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use GotChosen\SiteBundle\Entity\MassMailBatch;

abstract class AbstractBatchLimiter implements BatchLimiterInterface
{
    /** @var \Doctrine\ORM\EntityManager */
    protected $em;
    /** @var integer */
    protected $limit;

    public function __construct(Registry $registry)
    {
        $this->em = $registry->getManager();
    }

    /**
     * Sets the message limit.
     *
     * @param $limit
     * @return void
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * Adds to the number of messages sent in the current timeframe.
     *
     * @param \DateTime $currentDate
     * @param $count
     * @return void
     */
    public function addMessagesSent(\DateTime $currentDate, $count)
    {
        $repo = $this->em->getRepository('GotChosenSiteBundle:MassMailBatch');

        /** @var MassMailBatch $current */
        $current = $repo->findOneBy([
            'day'  => $currentDate,
            'hour' => $currentDate->format('G'),
        ]);

        if ( $current == null ) {
            $batch = new MassMailBatch();
            $batch->setDay($currentDate);
            $batch->setHour($currentDate->format('G'));
            $batch->setMessagesSent($count);
            $this->em->persist($batch);
        } else {
            $current->setMessagesSent($current->getMessagesSent() + $count);
        }

        $this->em->flush();
    }
}
