<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Translation
 *
 * @ORM\Table(name="translations",
 *            uniqueConstraints={ @ORM\UniqueConstraint(name="locale_key", columns={"locale", "translationKey"}) },
 *            indexes={ @ORM\Index(name="domain_idx", columns={"domain"}) }
 * )
 * @ORM\Entity(repositoryClass="GotChosen\SiteBundle\Repository\TranslationRepository")
 */
class Translation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=16)
     */
    private $locale;

    /**
     * @var string
     *
     * @ORM\Column(name="translationKey", type="string", length=255)
     */
    private $translationKey;

    /**
     * @var string
     *
     * @ORM\Column(name="translationText", type="text")
     */
    private $translationText;

    /**
     * @var string
     *
     * @ORM\Column(name="domain", type="string", length=32)
     */
    private $domain;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $locale
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set translationKey
     *
     * @param string $translationKey
     * @return Translation
     */
    public function setTranslationKey($translationKey)
    {
        $this->translationKey = $translationKey;
    
        return $this;
    }

    /**
     * Get translationKey
     *
     * @return string 
     */
    public function getTranslationKey()
    {
        return $this->translationKey;
    }

    /**
     * Set translationText
     *
     * @param string $translationText
     * @return Translation
     */
    public function setTranslationText($translationText)
    {
        $this->translationText = $translationText;
    
        return $this;
    }

    /**
     * Get translationText
     *
     * @return string 
     */
    public function getTranslationText()
    {
        return $this->translationText;
    }

    /**
     * Set domain
     *
     * @param string $domain
     * @return Translation
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    
        return $this;
    }

    /**
     * Get domain
     *
     * @return string 
     */
    public function getDomain()
    {
        return $this->domain;
    }
}
