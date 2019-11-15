<?php

namespace OrviSoft\FeedCronScripts\Cron;

class GoogleFeed
{
    protected $logger;
    protected $_helper;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \OrviSoft\FeedCronScripts\Helper\Data $data
     */
    public function __construct(\Psr\Log\LoggerInterface $logger, \OrviSoft\FeedCronScripts\Helper\Data $data)
    {
        $this->logger = $logger;
        $this->_helper = $data;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        $this->logger->addInfo("Cronjob GoogleFeed execution started.");
        $this->_helper->generateGoogleFeeds();
        $this->logger->addInfo("Cronjob GoogleFeed execution finished.");
    }
}
