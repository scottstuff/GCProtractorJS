<?php

namespace GotChosen\SiteBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * NotificationTypeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class NotificationTypeRepository extends EntityRepository
{
    public function getDefaults()
    {
        return $this->findBy(['isDefault' => true]);
    }
}