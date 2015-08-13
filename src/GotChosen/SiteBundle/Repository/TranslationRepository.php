<?php

namespace GotChosen\SiteBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use GotChosen\SiteBundle\Entity\Translation;

/**
 * TranslationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TranslationRepository extends EntityRepository
{
    public function kvByLocaleAndDomain($locale, $domain)
    {
        $q = $this->getEntityManager()->createQuery(
            'SELECT t.translationKey, t.translationText
             FROM GotChosenSiteBundle:Translation t
             WHERE t.locale = ?1 AND t.domain = ?2'
        );
        $q->setParameter(1, $locale);
        $q->setParameter(2, $domain);

        $data = $q->getArrayResult();
        $translations = [];
        foreach ( $data as $row ) {
            $translations[$row['translationKey']] = $row['translationText'];
        }

        return $translations;
    }

    public function findByLocaleAndDomain($locale, $domain)
    {
        return $this->findBy(['locale' => $locale, 'domain' => $domain]);
    }

    // after creating or updating a translation, be sure to run (in a controller)
    // $this->get('translator')->rebuildCache($locale);

    public function save($locale, $domain, $key, $text)
    {
        // ['{0} zero', '{1} one'] => '{0} zero|{1} one'
        if ( is_array($text) ) {
            $text = implode('|', $text);
        }

        /** @var Translation $trans */
        $trans = $this->findOneBy(['locale' => $locale, 'translationKey' => $key]);

        if ( !$trans ) {
            $trans = new Translation();
            $trans
                ->setLocale($locale)
                ->setTranslationKey($key)
                ->setDomain($domain)
                ->setTranslationText($text);

            $this->getEntityManager()->persist($trans);
        } else {
            $trans
                ->setDomain($domain)
                ->setTranslationText($text);
        }

        return $trans;
    }
}