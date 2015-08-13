<?php

namespace GotChosen\SiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class NewsController extends BaseController
{
    const ARTICLES_PER_PAGE = 5;

    /**
     * @Route("/news/{tab}/{page}", name="news", defaults={"tab" = "latest", "page" = 1}, requirements={"tab" = "[A-Za-z\-]+"})
     * @Template
     */
    public function archiveAction($tab = 'latest', $page = 1)
    {
        $articleRepo = $this->repo('NewsArticle');
        $recentArticles = $articleRepo->getRecentArticles(5);

        $categoryRepo = $this->repo('NewsCategory');
        $categories = $categoryRepo->findBy([], ['name' => 'ASC']);

        $currentCategory = $categoryRepo->findOneBy(['shortName' => $tab]);
        $numArticlesInCategory = $articleRepo->getNumberOfArticlesInCategory($currentCategory);

        $offset = self::ARTICLES_PER_PAGE * ($page - 1);

        $articles = $articleRepo->getArticles($offset, self::ARTICLES_PER_PAGE, $currentCategory);
        $numPages = ceil($articleRepo->getNumberOfArticlesInCategory($currentCategory) / self::ARTICLES_PER_PAGE);

        return [
            'tab'             => $tab,
            'categories'      => $categories,
            'recentArticles'  => $recentArticles,
            'articles'        => $articles,
            'currentCategory' => $currentCategory,
            'numPages'        => $numPages,
            'page'            => $page,
        ];
    }

    /**
     * @Route("/news/{id}/{slug}", name="news_article", requirements={"id" = "\d+"})
     * @Template
     */
    public function articleAction(Request $request, $id, $slug = '')
    {
        $articleRepo = $this->repo('NewsArticle');
        $recentArticles = $articleRepo->getRecentArticles(5);
        $article = $articleRepo->findOneById($id);

        return [
            'recentArticles' => $recentArticles,
            'article'        => $article,
        ];
    }
}
