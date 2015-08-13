<?php

namespace GotChosen\SiteBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetSecretKeyCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gotchosen:eg:setkey')
            ->setDescription('Assigns a specific secret key to a game')
            ->addArgument('gameId')
            ->addArgument('secretKey');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Registry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();
        
        $gameRepo = $em->getRepository('GotChosenSiteBundle:EGGame');
        $game = $gameRepo->find($input->getArgument('gameId'));
        
        if ( !$game ) {
            $output->writeln("Game not found.");
            return;
        }
        
        $output->writeln($game->getGameName());
        
        $secretKey = $input->getArgument('secretKey');
        $game->setSecretKey($secretKey);
        $em->flush();
        
        $output->writeln("Secret Key set to: {$secretKey}");
    }
}
