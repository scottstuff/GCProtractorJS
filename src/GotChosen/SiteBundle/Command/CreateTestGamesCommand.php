<?php

namespace GotChosen\SiteBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ObjectManager;
use GotChosen\SiteBundle\Entity\EGGame;
use GotChosen\SiteBundle\Entity\EGGameScholarships;
use GotChosen\SiteBundle\Entity\Scholarship;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateTestGamesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gotchosen:create-test-games')
            ->setDescription('Adds a set of test games to the database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // The 4 games from production that we'll be basing our test games off of.
        $gameData = [
            [
                'secretKey' => '095611a0c2c735355e099c15c836f157',
                'swfFile' => 'https://s3.amazonaws.com/ProdContent/evogames/games/2014/02/18/5303c96091eed.unity3d',
            ],
            [
                'secretKey' => '6529e08a1ded1e6da6e73421a4e75de7',
                'swfFile' => 'https://s3.amazonaws.com/ProdContent/evogames/games/2014/02/21/5307e1ac341e0.unity3d',
            ],
            [
                'secretKey' => 'c6d117f7c54467adefc7a6fad4d1847c',
                'swfFile' => 'https://s3.amazonaws.com/ProdContent/evogames/games/2014/02/22/5309585bf3b35.unity3d',
            ],
            [
                'secretKey' => 'df8f22de661514c530d76e80b22d3d98',
                'swfFile' => 'https://s3.amazonaws.com/ProdContent/evogames/games/2014/02/23/530abf78b9e5e.unity3d',
            ],
        ];

        $em = $this->getContainer()->get('doctrine')->getManager();
        $userManager = $this->getContainer()->get('fos_user.user_manager');
        $gsRepo = $em->getRepository('GotChosenSiteBundle:EGGameStats');

        // The usernames for the 10 users that we'll be basing our test games off of.
        foreach ( range(11, 20) as $n ) {
            $userData[] = $userManager->findUserByUsername("spattersontest{$n}");
        }

        $start = date_create('first day of this month')->setTime(0, 0, 0);
        $end   = date_create('last day of this month')->setTime(23, 59, 59);

        $newContest = new Scholarship();
        $newContest
            ->setScholarshipName("Evolution Games Contest")
            ->setScholarshipType(Scholarship::TYPE_EVOGAMES)
            ->setStartDate($start)
            ->setEndDate($end)
            ->setDrawingComplete(false);

        $em->persist($newContest);
        $em->flush();

        for ( $i = 0; $i < 10; $i++ ) {
            $thisGame = $gameData[$i % 4];
            $user = $userData[$i];
            $gameName = $user->getUsername() . ": Test Game " . $i + 1;

            $game = new EGGame();
            $game
                ->setUser($user)
                ->setSecretKey($thisGame['secretKey'])
                ->setSwfFile($thisGame['swfFile'])

                ->setGameName($gameName)
                ->setGameSynopsis("Test Game Synopsis")
                ->setStudioName("Test Studio")
                ->setStudioProfile("Test Studio Profile")

                ->setType(EGGame::TYPE_UNITY)
                ->setStatus(EGGame::STATUS_ACTIVE);

            $em->persist($game);
            $em->flush();

            $mapping = EGGameScholarships::make($game, $newContest, EGGameScholarships::TYPE_CONTEST);
            $em->persist($mapping);
            $game->addScholarship($mapping);

            // add game stats record if needed
            $gsRepo->getOrCreate($game, $start->format('Y-m'));
            $em->flush();
        }
    }
}
