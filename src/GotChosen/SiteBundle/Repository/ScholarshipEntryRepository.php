<?php

namespace GotChosen\SiteBundle\Repository;

use Doctrine\ORM\EntityRepository;
use GotChosen\SiteBundle\Entity\EntrySponsor;
use GotChosen\SiteBundle\Entity\Scholarship;
use GotChosen\SiteBundle\Entity\ScholarshipEntry;
use GotChosen\SiteBundle\Entity\User;

/**
 * ScholarshipEntryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ScholarshipEntryRepository extends EntityRepository
{
    /**
     * @param User $sponsor
     * @param Scholarship $sship40k
     * @return EntrySponsor[]
     */
    public function getSponsoring(User $sponsor, Scholarship $sship40k)
    {
        $em = $this->getEntityManager();

        $q = $em->createQuery(
            'SELECT es, e, u FROM GotChosenSiteBundle:EntrySponsor es
             JOIN es.user sponsor
             JOIN es.entry e
             JOIN e.user u
             JOIN e.scholarship s
             WHERE s.id = ?1 AND sponsor.id = ?2'
        );
        $q->setParameter(1, $sship40k->getId());
        $q->setParameter(2, $sponsor->getId());

        return $q->getResult();
    }

    public function getSponsors(ScholarshipEntry $entry, $offset = null, $limit = null)
    {
        $em = $this->getEntityManager();

        $q = $em->createQuery(
            'SELECT es, u FROM GotChosenSiteBundle:EntrySponsor es
             JOIN es.user u
             JOIN es.entry e
             WHERE e.id = ?1'
        );
        $q->setParameter(1, $entry->getId());

        if ( $limit !== null && $offset !== null ) {
            $q->setFirstResult($offset);
            $q->setMaxResults($limit);
        }

        return $q->getResult();
    }

    public function countSponsors(ScholarshipEntry $entry)
    {
        $em = $this->getEntityManager();

        $q = $em->createQuery(
            'SELECT COUNT(es) FROM GotChosenSiteBundle:EntrySponsor es
             JOIN es.entry e
             WHERE e.id = ?1'
        );
        $q->setParameter(1, $entry->getId());

        return $q->getSingleScalarResult();
    }
    
    public function getNumEntrants(Scholarship $scholarship)
    {
        $em = $this->getEntityManager();
        
        $q = $em->createQuery(
                'SELECT COUNT(e) FROM GotChosenSiteBundle:ScholarshipEntry e '
                . 'WHERE e.scholarship = ?1'
        );
        $q->setParameter(1, $scholarship->getId());
        
        return $q->getSingleScalarResult();
    }
}
