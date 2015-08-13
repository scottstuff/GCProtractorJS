<?php

namespace GotChosen\SiteBundle\Controller;

use FOS\UserBundle\Model\UserManager;
use GotChosen\SiteBundle\Entity\EGFeedback;
use GotChosen\SiteBundle\Entity\EGGame;
use GotChosen\SiteBundle\Entity\EGGameGenre;
use GotChosen\SiteBundle\Entity\EGGameStats;
use GotChosen\SiteBundle\Entity\EGPlayerStats;
use GotChosen\SiteBundle\Entity\EGPlaySession;
use GotChosen\SiteBundle\Entity\EGVote;
use GotChosen\SiteBundle\Entity\Scholarship;
use GotChosen\SiteBundle\Entity\User;
use GotChosen\SiteBundle\Repository\EGGameRepository;
use GotChosen\SiteBundle\Repository\EGPlayerStatsRepository;
use GotChosen\SiteBundle\Repository\EGVoteRepository;
use GotChosen\User\ReportCard;
use GotChosen\Util\Dates;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Util\SecureRandomInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class EGGamesController extends BaseController
{
    /**
     * @param Request $request
     * @return array
     *
     * @Route("/evolution-games/qualifier", name="eg_qualifier")
     * @Route("/evolution-games/qualifier/popular", name="eg_qualifier_popular")
     * @Template
     */
    public function qualifierAction(Request $request)
    {
        $route = $request->attributes->get('_route');

        $genre = $request->query->get('genre');
        $gameTitle = $request->query->get('game_title');
        $studio = $request->query->get('studio');

        $filteredBy = "";

        /** @var EGGameGenre $genre */
        if ( $genre and $genre = $this->repo('EGGameGenre')->find($genre) ) {
            $filteredBy .= ", " . $genre->getName();
        }
        else {
            $genre = null; // Make sure this is null to prevent errors
        }

        if ( $gameTitle ) {
            $filteredBy .= ", {$gameTitle}";
        }

        if ( $studio ) {
            $filteredBy .= ", {$studio}";
        }

        $egScholarship = $this->repo('Scholarship')->getCurrentEvoGames();

        /** @var EGGameRepository $gameRepo */
        $gameRepo = $this->repo('EGGame');

        $page = $request->query->getDigits('page', 1);
        $total = $gameRepo->countQualifierGames($egScholarship, $gameTitle, $studio, $genre);

        $limit = 20;
        $numPages = max(1, ceil($total / $limit));
        $page = max(1, min($numPages, $page));
        $offset = ($page - 1) * $limit;

        $minPage = max(1, $page - 2);
        $maxPage = min($numPages, $page + 2);

        /** @var Session $session */
        $session = $this->get('session');
        if ( !$session->has('qualifierSeed') ) { // this was easy
            $seed = microtime(true);
            $session->set('qualifierSeed', $seed);
        } else {
            $seed = $session->get('qualifierSeed');
        }

        $genres = $this->repo('EGGameGenre')->findAll();

        if ( $route == "eg_qualifier" ) {
            $qualifierGames = $gameRepo->findQualifierGames(
                $egScholarship, $gameTitle, $studio, $genre, $seed, $offset, $limit);
        }
        else {
            $qualifierGames = $gameRepo->findPopularQualifierGames(
                $egScholarship, $gameTitle, $studio, $genre, $offset, $limit);
            $filteredBy .= ", # of Plays";
        }

        $filteredBy = trim($filteredBy, ", ");

        return [
            'qualifierGames' => $qualifierGames,
            'genres' => $genres,
            'currentGenre' => $genre,
            'currentGameTitle' => $gameTitle,
            'currentStudio' => $studio,
            'filteredBy' => $filteredBy,

            'numPages' => $numPages,
            'page' => $page,
            'minPage' => $minPage,
            'maxPage' => $maxPage,
        ];
    }

    /**
     * @return array
     *
     * @Route("/evolution-games/contest", name="eg_contest")
     * @Template
     */
    public function contestAction()
    {
        return $this->redirectRoute('eg_scholarship');

        $egScholarship = $this->repo('Scholarship')->getCurrentEvoGames(false);

        if ( !$egScholarship ) {
            $this->flash("warning", "There is currently no Evolution Games contest running.");
            return $this->redirectRoute('eg_qualifier');
        }

        $contestGames = $this->repo('EGGame')->findContestGames($egScholarship);

        return ['contestGames' => $contestGames];
    }

    /**
     * @return array
     *
     * @Route("/evolution-games/contest-gate", name="eg_contest_gate")
     * @Template
     */
    public function contestGateAction(Request $request)
    {
        return $this->redirectRoute('eg_scholarship');

        $user = $this->getUser();
        $session = $request->getSession();
        $sship = $this->repo('Scholarship')->getCurrentEvoGames(false);

        if ( !$sship ) {
            $this->flash("warning", "There is currently no Evolution Games contest running.");
            return $this->redirectRoute('eg_qualifier');
        }

        if ( $user and $user->hasApplied($sship) ) {
            $session->set('cgateskip', true);
            return $this->redirectRoute('eg_contest');
        }

        if ( $session->get('cgateskip') == true ) {
            return $this->redirectRoute('eg_contest');
        }

        if ( $request->query->get('forfun') ) {
            $session->set('cgateskip', true);
            return $this->redirectRoute('eg_contest');
        }

        return [];
    }

    /**
     * @param Request $request
     * @param $id
     * @param string $slug
     * @return array
     *
     * @Route("/evolution-games/game/{id}/{slug}", name="eg_game")
     * @Template
     */
    public function gameAction(Request $request, $id, $slug = '')
    {

        $session = null;
        $sessionId = $request->query->get('session');
        if ( $sessionId !== null ) {
            // can use this to display the score the user got, or something?
            $session = $this->repo('EGPlaySession')->find($sessionId);
        }

        /** @var EGGame $game */
        $game = $this->repo('EGGame')->find($id);
        if ( !$game ) { // match slug too?
            $this->flash('error', "Uhh ...");
            return $this->redirectRoute('eg_qualifier');
        }

        $builder = $this->createFormBuilder();
        $builder
            ->add('thoughts', 'textarea', [
                'constraints' => [new NotBlank()],
            ]);

        $form = $builder->getForm();

        $form->handleRequest($request);
        if ( $this->getUser() && $form->isValid() ) {
            $newFeedback = new EGFeedback();
            $newFeedback->setGame($game)
                ->setFeedbackContent($form->get('thoughts')->getData())
                ->setUser($this->getUser());

            $this->em()->persist($newFeedback);
            $this->em()->flush();

            $this->flash('success', 'Thank you. Your feedback has been submitted.');
            return $this->redirectRoute('eg_game', ['id' => $game->getId(), 'slug' => $slug]);
        }

        $isInQualifier = $game->isInQualifier();

        /** @var EGVoteRepository $voteRepo */
        $voteRepo = $this->repo('EGVote');

        if ( $this->getUser() ) {
            /** @var ReportCard $reportCard */
            $reportCard = $this->get('gotchosen.report_card_manager')->getForUser($this->getUser());
        } else {
            $reportCard = null;
        }

        $userSession = $this->get('session')->getId();

        return [
            'gameId' => $game->getId(),
            //'gameSlug' => $slug,
            'game' => $game,
            'form' => $form->createView(),
            'isInQualifier' => $isInQualifier,
            'votesRemaining' => $voteRepo->getVotesRemaining($game, $request->getClientIp(),
                    $userSession, new \DateTime()),
            'reportCard' => $reportCard,
        ];
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/evolution-games/vote/{id}", name="eg_vote")
     * @Method("POST")
     */
    public function voteAction(Request $request, $id)
    {
        return $this->redirectRoute('eg_scholarship');

        $ip = $request->getClientIp();

        /** @var EGVoteRepository $voteRepo */
        $voteRepo = $this->repo('EGVote');

        /** @var EGGame $game */
        $game = $this->repo('EGGame')->find($id);

        if ( $game === null ) {
            return $this->renderJson([
                'status' => 'error',
                'message' => 'Game not found',
            ]);
        }

        $sessionId = $this->get('session')->getId();

        if ( !$voteRepo->canVoteOnGame($game, $ip, $sessionId, new \DateTime()) ) {
            return $this->renderJson([
                'status' => 'error',
                'message' => 'You have reached your vote limit for today',
            ]);
        }

        $remaining = $voteRepo->getVotesRemaining($game, $ip, $sessionId, new \DateTime());

        $vote = EGVote::make($game, $ip, $sessionId);
        $this->em()->persist($vote);

        /** @var EGGameStats $stats */
        $stats = $this->repo('EGGameStats')->getOrCreate($game, date('Y-m'));
        $stats->setLastUpdated(new \DateTime());
        $stats->setMonthVotes($stats->getMonthVotes() + 1);

        $game->setTotalVotes($game->getTotalVotes() + 1);

        $this->em()->flush();

        return $this->renderJson([
            'status' => 'ok',
            'votesRemaining' => $remaining - 1,
        ]);
    }

    /**
     * @param $id
     * @param string $slug
     * @return array
     *
     * @Route("/evolution-games/play/{id}/{slug}", name="eg_play")
     * @Template
     */
    public function playAction($id, $slug = '')
    {

        $user = $this->getUser();

        $game = $this->repo('EGGame')->find($id);
        $playSessionRepo = $this->repo('EGPlaySession');

        if ( !$game ) {
            $this->flash('error', "Uhh ...");
            return $this->redirectRoute('eg_qualifier');
        }

        /** @var SecureRandomInterface $random */
        $random = $this->get('security.secure_random');

        $phase = $game->getPlaySessionPhase();

        /**
         * Logic specifically for contest plays goes here.
         *
         * 1. Is this the first time they've ever played this game? Free play.
         * 2. Do they have 0 tokens? Free play.
         * 3. Is it not a free play and they have tokens? Subtract a token.
         */
        $freePlay = false;
        $noTokens = false;

        if ( $user and ($phase == EGPlaySession::PHASE_CONTEST or $phase == EGPlaySession::PHASE_CHAMPIONSHIP) ) {
            if ( !$playSessionRepo->findOneBy(['player' => $user->getId(), 'game' => $game->getId(), 'isCompleted' => true]) ) {
                $freePlay = true;
                $phase = EGPlaySession::PHASE_FREEPLAY;
            }

            if ( !$freePlay and $user->getTokens() == 0 ) {
                $noTokens = true;
                $phase = EGPlaySession::PHASE_FREEPLAY;
            }

            if ( !$freePlay and !$noTokens ) {
                $user->setTokens($user->getTokens() - 1);
            }
        }

        $session = null;

        // Don't create play sessions if not logged in.
        if ( $user ) {
            // Reuse an old play session if one's available?
            $session = $playSessionRepo->findOneBy([
                'player' => $user->getId(),
                'game' => $game->getId(),
                'phase' => $phase,
                'isCompleted' => false
            ]);

            if ( $session ) {
                $session->setStartDate(new \DateTime('now'));
            }
            else {
                $session = EGPlaySession::make($random, $phase, $game, $user);
                $this->em()->persist($session);
            }

            $this->em()->flush();
        }

        $gameParameters = [
            'session_id' => $session ? $session->getId() : null,
            'completion_url' => $this->generateUrl('eg_game', [
                'id' => $id,
                'slug' => $slug,
                'session' => $session ? $session->getId() : null,
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'api_endpoint' => (
                    strpos($this->getRequest()->getHttpHost(), 'gotchosen.dev') === false
                        ? 'https://'
                        : 'http://'
                    ) . $this->getRequest()->getHttpHost() . '/evolution-games/api/v1'
        ];

        $flashVars = http_build_query([
            'session_id' => $gameParameters['session_id'],
            'completion_url' => $gameParameters['completion_url'],
            'api_endpoint' => $gameParameters['api_endpoint']
        ]);

        return [
            'flashVars' => $flashVars,
            'gameParameters' => $gameParameters,
            'game' => $game,
            'gameId' => $id,
            'freePlay' => $freePlay,
            'noTokens' => $noTokens,
        ];
    }

    /**
     * @param Request $request
     * @param $username
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/evolution-games/profile/{username}", name="eg_profile")
     * @Route("/evolution-games/profile-id/{username}", name="eg_profile_id")
     * @Template
     */
    public function profileAction(Request $request, $username)
    {
        return $this->redirectRoute('eg_scholarship');

        // We're apparently not using this anymore. Let's put this here just in
        // case we missed any links.
        return $this->redirectRoute('user_profile', ['username' => $username]);

        /*
         * i.  Public Profile page will display gamer profile in addition to: Games played
         *     with win/loss stats. Total number of points in the competition (report card), and global
         *     ranking. In addition, we can display their picture/avatar, their bio/story. Facebook
         *     posting functionality (but it will show only to the player, player must be logged in
         *     GotChosen to see).
         *
         * ii. When player is logged in, it will display the number of remaining plays for the day, and
         *     bonus plays (if any) along with pending challenge requests/challenge initiations. It will
         *     also Display the history of the rounds: the game played, the day played, and the score.
         */

        $route = $request->attributes->get('_route');

        /** @var UserManager $userManager */
        $userManager = $this->get('fos_user.user_manager');

        /** @var User $user */
        if ( $route == 'eg_profile' ) {
            $user = $userManager->findUserByUsername($username);

            // We're probably not supposed to do this, but ...
            if ( !$user ) {
                $user = $userManager->findUserBy(['username' => $username]);
            }
        } else {
            $user = $userManager->findUserBy(['id' => $username]);
        }

        if ( !$user || !$user->isEnabled() ) {
            throw new NotFoundHttpException('User not found');
        }

        $this->repo('User')->precacheProperties($user);

        /** @var EGPlayerStatsRepository $statsRepo */
        $statsRepo = $this->repo('EGPlayerStats');

        $reportCard = $this->get('gotchosen.report_card_manager')->getForUser($user);

        $gameResults = $this->repo('EGGameResult')->findResultsForProfile($user, date('Y-m'));

        $gameSessions = [];
        foreach ( Dates::rangeMonths(date('Y-m'), -2) as $month ) {
            $gameSessions["$month-01"] = $statsRepo->findPlaySessions($user, $month);
        }

        return [
            'user' => $user,
            'reportCard' => $reportCard,
            'gameSessions' => $gameSessions,
            'gameResults' => $gameResults,
        ];
    }

    /**
     * @return array
     *
     * @Route("/evolution-games/wall-of-fame", name="eg_wall")
     * @Template
     */
    public function wallAction()
    {
        /** @var EGGameRepository $gameRepo */
        $gameRepo = $this->repo('EGGame');
        /** @var EGPlayerStatsRepository $pstatsRepo */
        $pstatsRepo = $this->repo('EGPlayerStats');

        $scholarship = $this->repo('Scholarship')->getCurrentEvoGames();
        $prevScholarship = $this->repo('Scholarship')->getEvoGamesByDate(date_create('-1 month'));
        $prev2Scholarship = $this->repo('Scholarship')->getEvoGamesByDate(date_create('-2 month'));

        $curMonth = date('Y-m');
        $prevMonth = Dates::prevMonth($curMonth);
        $prev2Month = Dates::prevMonth($prevMonth);

        $currentQualifier = $gameRepo->findQualifierGamesByRank($scholarship, $curMonth);
        $currentDev = $gameRepo->findContestGamesByRank($scholarship, $curMonth);
        $currentChamps = $pstatsRepo->getChampionshipLeaders($scholarship, $curMonth, 10);

        // cache these later, they don't really change
        if ( $prevScholarship ) {
            $prevQualifier = $gameRepo->findQualifierGamesByRank($prevScholarship, $prevMonth);
            $prevDev = $gameRepo->findContestGamesByRank($prevScholarship, $prevMonth, 2);
            $prevChamps = $pstatsRepo->getChampionshipLeaders($prevScholarship, $prevMonth, 1);
        } else {
            $prevQualifier = $prevDev = $prevChamps = [];
        }

        if ( $prev2Scholarship ) {
            $prev2Qualifier = $gameRepo->findQualifierGamesByRank($prev2Scholarship, $prev2Month);
            $prev2Dev = $gameRepo->findContestGamesByRank($prev2Scholarship, $prev2Month, 2);
            $prev2Champs = $pstatsRepo->getChampionshipLeaders($prev2Scholarship, $prev2Month, 1);
        } else {
            $prev2Qualifier = $prev2Dev = $prev2Champs = [];
        }

        $users = [];
        foreach ( $currentChamps as $u ) {
            $users[] = $u->getPlayer();
        }
        foreach ( $prevChamps as $u ) {
            $users[] = $u->getPlayer();
        }
        foreach ( $prev2Champs as $u ) {
            $users[] = $u->getPlayer();
        }

        $this->repo('User')->precachePropertiesMulti($users, ['PhotoURL']);

        return [
            'currentQualifier' => $currentQualifier,
            'currentDev' => $currentDev,
            'currentChamps' => $currentChamps,

            'prevMonth' => $prevMonth,
            'prevQualifier' => $prevQualifier,
            'prevDev' => $prevDev,
            'prevChamps' => $prevChamps,

            'prev2Month' => $prev2Month,
            'prev2Qualifier' => $prev2Qualifier,
            'prev2Dev' => $prev2Dev,
            'prev2Champs' => $prev2Champs,
        ];
    }
}
