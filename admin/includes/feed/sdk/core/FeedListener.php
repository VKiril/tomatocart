<?php

interface FeedListener
{
    /**
     * @param Feed $container
     * @return mixed
     */
    public function setContainer(Feed $container);

    /**
     * @return Feed
     */
    public function getContainer();
}
