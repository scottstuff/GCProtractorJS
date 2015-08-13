<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NewsArticleContent
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class NewsArticleContent
{
    /**
     * @var NewsArticle
     *
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="NewsArticle")
     */
    private $article;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * Set article
     *
     * @param NewsArticle $article
     * @return NewsArticleContent
     */
    public function setArticle($article)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * Get article
     *
     * @return NewsArticle
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return NewsArticleContent
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
