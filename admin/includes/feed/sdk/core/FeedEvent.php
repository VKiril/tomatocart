<?php

class FeedEvent
{
    /** @var int */
    protected $status;

    /** @var HttpResponse */
    protected $response;

    /** @var $dispacher FeedListener */
    protected $dispacher;

    /**
     * @param FeedListener $dispacher
     */
    public function setDispacher(FeedListener $dispacher) {
        $this->dispacher = $dispacher;
    }

    /**
     * @return FeedListener
     */
    public function getDispacher() {
        return $this->dispacher;
    }

    /**
     * @param \HttpResponse $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @return \HttpResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }
}
