<?php

namespace MageOS\MaxMindGeoipRedirect\Console\Command;

use Symfony\Component\Console\Command\Command;
use MageOS\MaxMindGeoipRedirect\Api\GeoloateIPInterface;
use MageOS\MaxMindGeoipRedirect\Helper\ModuleConfig;
use Magento\Directory\Model\CountryFactory;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;

class GeolocateIP extends Command
{
    /**
     * @param GeoloateIPInterface $geoloateIP
     * @param ModuleConfig $moduleConfig
     * @param CountryFactory $countryFactory
     * @param string|null $name
     */
    public function __construct(
        protected GeoloateIPInterface $geoloateIP,
        protected ModuleConfig $moduleConfig,
        protected CountryFactory $countryFactory,
        string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('maxmind:geolite2:geolocate');
        $this->setDescription('Given an IP address, returns the Country in which it was geolocated');
        $this->addOption(
            'ip',
            '',
            InputOption::VALUE_REQUIRED,
            'IPv4 or IPv6 address'
        );
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        if (empty($input->getOption('ip'))) {
            throw new Exception('IP address is required.');
        }

        if (!$this->moduleConfig->isEnable()) {
            throw new Exception('This module is not enabled. Check the configuration and try again.');
        }

        $countryCode = $this->geoloateIP->execute($input->getOption('ip'));

        if (empty($countryCode)) {
            $output->writeln('<error>We are unable to geolocate this IP.</error>');
            return Command::FAILURE;
        }

        $country = $this->countryFactory->create()->loadByCode($countryCode);
        $output->writeln(sprintf('<info>IP address country: %s</info>', $country->getName()));

        return Command::SUCCESS;
    }
}
