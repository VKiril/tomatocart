<?php

class FeedBaseListener implements FeedListener
{
    /** @var Feed */
    protected $container;

    /**
     * @param Feed $container
     */
    public function __construct(Feed $container)
    {
        $this->container = $container;
    }

    public function onRegisterFeed(FeedEvent $event)
    {
        $event->setResponse($this->container->api->registerUser());
    }

    public function onNewsFeed(FeedNewsEvent $event)
    {
        $event->setResponse($this->container->api->getLastNews());
    }

    /**
     * @param \Feed $container
     * @return mixed|void
     */
    public function setContainer(Feed $container)
    {
        $this->container = $container;
    }

    /**
     * @return \Feed
     */
    public function getContainer()
    {
        return $this->container;
    }
}
