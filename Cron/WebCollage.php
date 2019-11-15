<?php


namespace OrviSoft\FeedCronScripts\Cron;

class WebCollage
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
        $this->logger->addInfo("Cronjob WebCollage feed execution started.");
        $this->_helper->generateWebCollageFeeds();
        $this->logger->addInfo("Cronjob WebCollage execution finished.");
    }
}
