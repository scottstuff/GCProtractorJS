<?php

namespace GotChosen\SiteBundle\Command;

use GotChosen\SiteBundle\Localization\Translator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TranslatorCacheCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gotchosen:translator:cache')
            ->addArgument('locale', InputArgument::OPTIONAL, 'Locale to cache (en, es, pt). Defaults to all.', '')
            ->setDescription('Build the translator cache.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $locale = $input->getArgument('locale');

        /** @var Translator $translator */
        $translator = $this->getContainer()->get('translator');

        if ( $locale ) {
            $output->write("Rebuilding translation cache for $locale... ");
            $translator->rebuildCache($locale);
            $translator->rebuildMemcache($locale);
            $output->writeln('Done.');
        } else {
            foreach ( ['en', 'es', 'pt'] as $locale ) {
                $output->write("Rebuilding translation cache for $locale... ");
                $translator->rebuildCache($locale);
                $translator->rebuildMemcache($locale);
                $output->writeln('Done.');
            }
        }
    }
}