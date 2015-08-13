<?php

namespace GotChosen\SiteBundle\Localization;

use Doctrine\Common\Persistence\ObjectManager;
use GotChosen\SiteBundle\Repository\TranslationRepository;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Resource\ResourceInterface;
use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Translation\MessageCatalogue;

class TranslationORMLoader implements LoaderInterface
{
    /**
     * @var TranslationRepository
     */
    private $translationRepo;

    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->translationRepo = $manager->getRepository('GotChosenSiteBundle:Translation');
    }

    /**
     * Loads a locale.
     *
     * @param mixed $resource A resource
     * @param string $locale   A locale
     * @param string $domain   The domain
     *
     * @return MessageCatalogue A MessageCatalogue instance
     *
     * @throws NotFoundResourceException when the resource cannot be found
     * @throws InvalidResourceException  when the resource cannot be loaded
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        $translations = $this->translationRepo->kvByLocaleAndDomain($locale, $domain);

        $catalogue = new MessageCatalogue($locale);
        $catalogue->add($translations, $domain);

        return $catalogue;
    }
}