<?php

namespace GotChosen\SiteBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Util\SecureRandom;

class GenerateSecretKeyCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gotchosen:eg:genkey')
            ->setDescription('Generates a secret key for use with the EG API');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $random = new SecureRandom();
        $key = bin2hex($random->nextBytes(16));
        $output->writeln("Secret key: {$key}");
    }
}
