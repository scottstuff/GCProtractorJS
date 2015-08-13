<?php

namespace GotChosen\SiteBundle\Localization;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Bundle\FrameworkBundle\Translation\Translator as BaseTranslator;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Translation\MessageSelector;

/**
 * Customized translator that can dump the translation cache at will (after DB changes, mostly).
 * $this->get('translator')->rebuildCache($locale);
 *
 * @package GotChosen\SiteBundle\Localization
 */
class Translator extends BaseTranslator
{
    /**
     * @var \Memcached
     */
    protected $memcached;

    public function __construct(ContainerInterface $container, MessageSelector $selector, $loaderIds = array(),
                                array $options = array())
    {
        parent::__construct($container, $selector, $loaderIds, $options);

        $this->memcached = $container->get('php.memcache');
    }

    /**
     * This version of loadCatalogue should pull data out of memcached.
     */
    protected function loadCatalogue($locale)
    {
        if ( isset($this->catalogues[$locale]) ) {
            return;
        }

        if ( null === $this->options['cache_dir'] ) {
            $this->initialize();
            \Symfony\Component\Translation\Translator::loadCatalogue($locale);
            return;
        }

        if ( $this->options['debug'] ) {
            //$this->initialize();
            //$this->rebuildMemcache($locale);
        }

        $this->loadLocaleFromMemcache($locale);
    }

    public function loadLocaleFromMemcache($locale)
    {
        $data = $this->memcached->get("trans.$locale", function($memcache, $key, &$value) use ($locale) {
            $value = $this->getDataToCache($locale);
            return true;
        });

        $ct = new MessageCatalogue($locale, $data['catalogue']);
        foreach ( $data['fallbacks'] as $fallback => $fallbackMessages ) {
            $ct->addFallbackCatalogue(new MessageCatalogue($fallback, $fallbackMessages));
        }

        $this->catalogues[$locale] = $ct;
    }

    protected function getDataToCache($locale)
    {
        $this->initialize();

        \Symfony\Component\Translation\Translator::loadCatalogue($locale);

        $fallbacks = [];
        foreach ( $this->computeFallbackLocales($locale) as $fallback ) {
            $fallbacks[$fallback] = $this->catalogues[$fallback]->all();
        }

        return [
            'catalogue' => $this->catalogues[$locale]->all(),
            'fallbacks' => $fallbacks,
        ];
    }

    public function rebuildMemcache($locale)
    {
        $this->memcached->set("trans.$locale", $this->getDataToCache($locale));
    }

    public function rebuildCache($locale)
    {
        $cache = new ConfigCache($this->options['cache_dir'].'/catalogue.'.$locale.'.php', $this->options['debug']);
        $this->initialize();

        // one of the rare situations to "skip" to the grandparent implementation.
        \Symfony\Component\Translation\Translator::loadCatalogue($locale);

        $fallbackContent = '';
        $current = '';
        foreach ($this->computeFallbackLocales($locale) as $fallback) {
            $fallbackSuffix = ucfirst(str_replace('-', '_', $fallback));

            $fallbackContent .= sprintf(<<<EOF
\$catalogue%s = new MessageCatalogue('%s', %s);
\$catalogue%s->addFallbackCatalogue(\$catalogue%s);


EOF
                ,
                $fallbackSuffix,
                $fallback,
                var_export($this->catalogues[$fallback]->all(), true),
                ucfirst(str_replace('-', '_', $current)),
                $fallbackSuffix
            );
            $current = $fallback;
        }

        $content = sprintf(<<<EOF
<?php

use Symfony\Component\Translation\MessageCatalogue;

\$catalogue = new MessageCatalogue('%s', %s);

%s
return \$catalogue;

EOF
            ,
            $locale,
            var_export($this->catalogues[$locale]->all(), true),
            $fallbackContent
        );

        $cache->write($content, $this->catalogues[$locale]->getResources());
    }
}