<?php

namespace GotChosen\SiteBundle\Controller\Admin;

use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Model\UserManager;
use GotChosen\SiteBundle\Controller\BaseController;
use GotChosen\SiteBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use GotChosen\SiteBundle\Entity\NewsArticle;
use GotChosen\SiteBundle\Entity\NewsArticleContent;
use GotChosen\SiteBundle\Entity\NewsCategory;
use GotChosen\SiteBundle\Entity\NewsCategoryRepository;

/**
 * Class NewsController
 * @package GotChosen\SiteBundle\Controller\Admin
 *
 * @Route(options={"i18n" = false})
 */
class NewsController extends BaseController
{
    const ARTICLES_PER_PAGE = 10;

    /**
     * @Route("/admin/news/{page}", requirements={"page" = "\d+"}, name="admin_news")
     * @Template
     */
    public function archiveAction($page = 1)
    {
        $newsRepo = $this->repo('NewsArticle');

        $offset = self::ARTICLES_PER_PAGE * ($page - 1);
        $numPages = ceil($newsRepo->getNumberOfArticles() / self::ARTICLES_PER_PAGE);

        $articles = $newsRepo->findBy([], ['dateCreated' => 'DESC'],
                                        self::ARTICLES_PER_PAGE,
                                        $offset);

        return [
            'articles' => $articles,
            'page' => $page,
            'numPages' => $numPages,
        ];
    }

    /**
     * @Route("/admin/news/create", name="admin_news_create")
     * @Template
     */
    public function createAction(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        /** @var UserManager $userManager */
        $userManager = $this->get('fos_user.user_manager');

        $catRepo = $this->repo('NewsCategory');
        $categories = $catRepo->findBy([], ['name' => 'ASC']);
        $categoryChoices = array();
        foreach ($categories as $category)
        {
            $categoryChoices[$category->getShortName()] = $category->getName();
        }

        $formData = [
            'publishDate' => (new \DateTime())->format('n/j/Y'),
        ];
        $fb = $this->createFormBuilder($formData);

        $fb
            ->add('category', 'choice', [
                'choices' => $categoryChoices,
            ])
            ->add('title', 'text')
            ->add('content', 'textarea')
            ->add('publish', 'checkbox', [
                'required' => false,
                'label' => 'Publish this article?',
                'render_optional_text' => false,
                'widget_checkbox_label' => 'label',
            ])
            ->add('publishDate', 'text');

        $form = $fb->getForm();

        $form->handleRequest($request);
        if ( $form->isValid() ) {
            $a = new NewsArticle();
            $a->setTitle($form->get('title')->getData());
            $a->setAuthor($currentUser);
            $a->setCategory($catRepo->findOneBy(['shortName' => $form->get('category')->getData()]));
            $a->setDateCreated(new \DateTime());
            $a->setLastModified(new \DateTime());
            $a->setPublishDate(new \DateTime($form->get('publishDate')->getData()));
            $a->setPublished($form->get('publish')->getData());
            $this->em()->persist($a);
            $this->em()->flush();

            $c = new NewsArticleContent();
            $c->setContent($form->get('content')->getData());
            $c->setArticle($a);
            $this->em()->persist($c);
            $this->em()->flush();

            $this->flash('success', 'News Article Created');
            return $this->redirectRoute('admin_news');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/admin/news/edit/{id}", name="admin_news_edit")
     * @Template
     */
    public function editAction($id)
    {
        $request = $this->getRequest();

        $articleRepo = $this->repo('NewsArticle');
        $article = $articleRepo->findOneById($id);

        if ($article)
        {
            $formData = [
                'category'    => $article->getCategory()->getShortName(),
                'title'       => $article->getTitle(),
                'content'     => $article->getContent()->getContent(),
                'publish'     => $article->isPublished(),
                'publishDate' => $article->getPublishDate()->format('n/j/Y'),
            ];

            $catRepo = $this->repo('NewsCategory');
            $categories = $catRepo->findBy([], ['name' => 'ASC']);
            $categoryChoices = array();
            foreach ($categories as $category)
            {
                $categoryChoices[$category->getShortName()] = $category->getName();
            }

            $fb = $this->createFormBuilder($formData);
            $fb
                ->add('category', 'choice', [
                    'choices' => $categoryChoices,
                ])
                ->add('title', 'text')
                ->add('content', 'textarea')
                ->add('publish', 'checkbox', [
                    'required' => false,
                    'label' => 'Publish this article?',
                    'render_optional_text' => false,
                    'widget_checkbox_label' => 'label',
                ])
                ->add('publishDate', 'text');

            $form = $fb->getForm();

            $form->handleRequest($request);
            if ( $form->isValid() ) {
                $article->setTitle($form->get('title')->getData());
                $article->setAuthor($currentUser);
                $article->setCategory($catRepo->findOneBy(['shortName' => $form->get('category')->getData()]));
                $article->setLastModified(new \DateTime());
                $article->setPublishDate(new \DateTime($form->get('publishDate')->getData()));

                $article->setPublished($form->get('publish')->getData());

                $this->em()->persist($article);
                $this->em()->flush();

                $content = $article->getContent();
                $content->setContent($form->get('content')->getData());

                $this->em()->persist($content);
                $this->em()->flush();

                $this->flash('success', 'News Article Updated');
                return $this->redirectRoute('admin_news');
            }

            return [
                'form' => $form->createView(),
            ];
        }
        else
        {
            $this->flash('error', 'Could not find news article requested');
            return $this->redirectRoute('admin_news');
        }
    }

    /**
     * @Route("/admin/news/delete/{id}", name="admin_news_delete")
     */
    public function deleteAction($id)
    {
        $articleRepo = $this->repo('NewsArticle');
        $article = $articleRepo->findOneById($id);

        if ($article)
        {
            $this->em()->remove($article->getContent());
            $this->em()->remove($article);
            $this->em()->flush();

            $this->flash('success', 'News Article Deleted');
        }
        else
        {
            $this->flash('error', 'Could not find news article requested');
        }

        return $this->redirectRoute('admin_news');
    }
}
