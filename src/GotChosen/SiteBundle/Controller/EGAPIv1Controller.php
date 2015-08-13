<?php

namespace GotChosen\SiteBundle\Controller;

use GotChosen\SiteBundle\Entity\EGGame;
use GotChosen\SiteBundle\Entity\EGGameResult;
use GotChosen\SiteBundle\Entity\EGGameStats;
use GotChosen\SiteBundle\Entity\EGPlaySession;
use GotChosen\SiteBundle\Event\GameSessionEvent;
use GotChosen\SiteBundle\GCSiteEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API Controller, version 1 - https://gotchosen.com/evolution-games/api/v1/{method}/{args...}
 *
 * @package GotChosen\SiteBundle\Controller
 *
 * @Route("/evolution-games/api/v1", options={"i18n" = false})
 */
class EGAPIv1Controller extends BaseController
{
    const ERR_JSON_INVALID = 'JSON_INVALID';
    const ERR_SESSION_ID_INVALID = 'SESSION_ID_INVALID';
    const ERR_SESSION_NOT_FOUND = 'SESSION_NOT_FOUND';
    const ERR_INVALID_SCORE = 'INVALID_SCORE';
    const ERR_INVALID_HASH = 'INVALID_HASH';

    /**
     * POST /evolution-games/api/v1/play-complete
     *
     * Parameters:
     * - session_id [string] = The 40-character hexadecimal play session ID.
     * - score [float] = The player's score, greater than or equal to 0.
     * - hash [string] = The hmac-sha256 hash using "session_id:score" as the data, and your game's
     *                   secret key as the key. Encode the hash as hexadecimal digits (should have length = 64).
     *
     * Returns:
     * - {"status":"ok"} If everything is fine, and the score/play submitted successfully
     * - {"status":"error","errorCode":"code"} In case of an error. See error codes section.
     *
     * Error Codes:
     * - SESSION_ID_INVALID: The session id is not 40 characters long, or contains characters outside of a-f 0-9
     * - INVALID_SCORE: Score could not be parsed as a numerical value, or is less than 0
     * - SESSION_NOT_FOUND: The play session for the user was not found
     * - INVALID_HASH: The hmac-sha256 value of "session_id:score" hashed with your game's secret key was invalid
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/play-complete")
     * @Method("POST")
     */
    public function playCompleteAction(Request $request)
    {
        $jsonContent = $request->getContent();
        $parameters = json_decode($jsonContent, true);

        if ( $parameters === null ) {
            return $this->renderJsonError(self::ERR_JSON_INVALID);
        }

        $sessionId = isset($parameters['session_id']) ? $parameters['session_id'] : '';
        $score = filter_var(isset($parameters['score']) ? $parameters['score'] : -1, FILTER_VALIDATE_FLOAT);
        $hash = isset($parameters['hash']) ? $parameters['hash'] : '';

        // check session ID formatting
        if ( strlen($sessionId) !== 40 || !preg_match('/^[0-9a-f]+$/', $sessionId) ) {
            return $this->renderJsonError(self::ERR_SESSION_ID_INVALID);
        }

        // check score is numeric and >= 0
        if ( $score === false || $score < 0 ) {
            return $this->renderJsonError(self::ERR_INVALID_SCORE);
        }

        $sessions = $this->repo('EGPlaySession');

        // check if the play session is valid

        /** @var EGPlaySession $playSession */
        $playSession = $sessions->find($sessionId);
        if ( !$playSession ) {
            return $this->renderJsonError(self::ERR_SESSION_NOT_FOUND);
        }

        // check if the given hmac-sha256 hash of the data is valid
        // I mean ... sha1 hash. Did I say hmac? sha256? Oops.
        $game = $playSession->getGame();
        $hashData = $sessionId . ':' . $score . ':' . $game->getSecretKey();
        $checkHash = sha1($hashData);
        if ( $checkHash !== $hash ) {
            return $this->renderJsonError(self::ERR_INVALID_HASH);
        }

        $playSession->setIsCompleted(true);
        $playSession->setEndDate(new \DateTime('now'));
        $playSession->setScore($score);

        /** @var EGGameStats $gameStats */
        $gameStats = $this->repo('EGGameStats')->getOrCreate($game, date('Y-m'));
        $gameStats->setMonthPlays($gameStats->getMonthPlays() + 1);
        $gameStats->setLastUpdated(new \DateTime());

        if ( $playSession->getPlayer() ) {
            /** @var EGGameResult $gameResult */
            $gameResult = $this->repo('EGGameResult')->getOrCreate($game, $playSession->getPlayer(), date('Y-m'));
            $gameResult->setPlays($gameResult->getPlays() + 1);
        }

        $game->setTotalPlays($game->getTotalPlays() + 1);
        
        if ( $game->getStatus() == EGGame::STATUS_NO_API_CONNECT ) {
            $game->setStatus(EGGame::STATUS_ACTIVE);
        }

        // this was probably just a waste, may put all code right in this file.
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->get('event_dispatcher');
        $dispatcher->dispatch(GCSiteEvents::EG_GAME_SESSION_COMPLETED, new GameSessionEvent($playSession));

        $this->em()->flush();

        return $this->renderJson([
            'status' => 'ok',
        ]);
    }

    protected function renderJsonError($code)
    {
        return $this->renderJson(['status' => 'error', 'errorCode' => $code]);
    }
}