<?php

class FeedNewsEvent extends FeedEvent
{
    public function getNews()
    {
        $body = $this->getResponse()->getBody();
        $data = json_decode($body);
        if ($data) {
            return $data->data;
        }

        return null;
    }
}
