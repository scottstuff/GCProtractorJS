<?php

namespace GotChosen\SiteBundle\Command;

use Doctrine\ORM\EntityManager;
use GotChosen\Mail\Processor;
use GotChosen\SiteBundle\Entity\MassMailQueue;
use GotChosen\SiteBundle\Repository\MassMailQueueRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MassMailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gotchosen:massmail')
            ->setDescription('Processes the mass mail queue')
            ->addOption('simulate', null, InputOption::VALUE_NONE, 'Do not send, only report')
            ->addOption('batch', null, InputOption::VALUE_REQUIRED, 'Number of users to fetch per iteration', 100)
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Maximum number of users to send to per-day');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pidFile = $this->getContainer()->getParameter('kernel.cache_dir') . '/gc_massmail.pid';
        if ( $this->isProcessRunning($pidFile, $output) ) {
            $output->writeln('PID file exists, command still running? Quitting.');
            return;
        }
        file_put_contents($pidFile, getmypid());

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        /** @var MassMailQueueRepository $mmRepo */
        $mmRepo = $em->getRepository('GotChosenSiteBundle:MassMailQueue');
        /** @var Processor $processor */
        $processor = $this->getContainer()->get('gotchosen.mail.processor');

        if ( $input->getOption('simulate') ) {
            $output->writeln('Simulation Mode');
            $processor->enableSimulation();
        }
        $processor->setOutput($output);

        $batchSize = (int) $input->getOption('batch');
        if ( $batchSize <= 0 ) {
            throw new \InvalidArgumentException('"--batch" option must be >= 1');
        }
        
        $limit = (int) $input->getOption('limit');
        if ( $limit > 0 ) {
            $mailLimiter = $this->getContainer()->get('gotchosen.mail.limiter');
            $mailLimiter->setLimit($limit);
            $remaining = $mailLimiter->getMessagesRemaining(new \DateTime('now'));
            $output->writeln("Setting daily message limit to: {$limit} (remaining: {$remaining})");
        }

        $newEntries = $mmRepo->findReadyToProcess();
        foreach ( $newEntries as $entry ) {
            try {
                $processor->process($entry, $batchSize);
            } catch ( \Exception $e ) {
                $entry->setStatus(MassMailQueue::STATUS_ERROR);
                $entry->setErrorReason($e->__toString());
                $em->flush();
            }
        }

        unlink($pidFile);
    }

    private function isProcessRunning($pidFile, OutputInterface $output)
    {
        if ( file_exists($pidFile) ) {
            $pid = trim(file_get_contents($pidFile));
            $pids = explode("\n", trim(`ps -e | awk '{print $1}'`));

            if ( in_array($pid, $pids) ) {
                return true;
            }

            $output->writeln('Removing stale lock file.');
            unlink($pidFile);
        }

        return false;
    }
} 