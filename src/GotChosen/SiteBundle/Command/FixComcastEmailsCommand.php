<?php

namespace GotChosen\SiteBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use GotChosen\SiteBundle\Entity\User;
use GotChosen\SiteBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixComcastEmailsCommand extends ContainerAwareCommand
{
    protected $total = 0;
    protected $output;
    protected $em;
    protected $userRepo;
    
    protected function configure()
    {
        $this
            ->setName('gotchosen:fix-comcast-emails')
            ->setDescription('Queries Mailgun API to find Comcast spam rejections and fixes their user status.');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->userRepo = $this->em->getRepository('GotChosenSiteBundle:User');
        
        // Probably shouldn't store this in code ...
        $apiKey = "key-24bkrkyya44p6myssfde0ls4um7e4jt1";
        $auth = sprintf('Authorization: Basic %s', base64_encode("api:{$apiKey}"));
        
        $query = http_build_query([
            'event' => 'failed',
            'begin' => 'Fri, 4 April 2014 00:00:00 -0000',
            'end' => 'Sat, 5 April 2014 00:00:00 -0000'
        ]);
        
        $opts = ['http' => [
            'method' => 'GET',
            'header' => $auth
        ]];
        $context = stream_context_create($opts);
        
        $json = file_get_contents("https://api.mailgun.net/v2/gotchosen.com/events?{$query}", false, $context);
        $jsonData = json_decode($json, true);
        
        $nextPage = $this->processJsonData($jsonData);
        
        while ( $nextPage ) {
            $json = file_get_contents($nextPage, false, $context);
            $jsonData = json_decode($json, true);
            $nextPage = $this->processJsonData($jsonData);
        }
        
        $this->output->writeln("{$this->total} users updated.");
    }
    
    protected function processJsonData($jsonData)
    {
        if ( empty($jsonData['items']) ) {
            return false;
        }
        
        foreach ( $jsonData['items'] as $item ) {
            if ( $item['recipient-domain'] != 'comcast.net' ) {
                continue;
            }
            
            $email = strtolower($item['recipient']);
            
            $this->output->writeln($email);
            
            $user = $this->userRepo->findOneBy(['emailCanonical' => $email]);
            if ( !$user ) {
                $this->output->writeln("  - User not found or not disabled.");
                continue;
            }
            
            $this->output->write("  - Fixing user status ... ");
            
            $user->setEnabled(true);
            $user->setStatus(User::STATUS_ACTIVE);
            $this->em->flush();
            
            $this->output->writeln("Done.");
            $this->total++;
        }
        
        return $jsonData['paging']['next'];
    }
}
