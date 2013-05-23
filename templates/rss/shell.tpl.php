<?php

    header('Content-type: application/rss+xml');
    unset($vars['body']);
    //$vars['messages'] = \Idno\Core\site()->session()->getAndFlushMessages();

    $page = new DOMDocument();
    $page->formatOutput = true;
    $rss = $page->createElement('rss');
    $rss->setAttribute('version', '2.0');
    $rss->setAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
    $rss->setAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
    $channel = $page->createElement('channel');
    $channel->appendChild($page->createElement('title',$vars['title']));
    $channel->appendChild($page->createElement('description',$vars['description']));
    $channel->appendChild($page->createElement('link',str_replace('?_t=rss','',str_replace('&_t=rss','',\Idno\Core\site()->config()->url . substr($_SERVER['REQUEST_URI'],1)))));
    $self = $page->createElement('atom:link');
    $self->setAttribute('href', \Idno\Core\site()->config()->url . substr($_SERVER['REQUEST_URI'],1));
    $self->setAttribute('rel','self');
    $self->setAttribute('type', 'application/rss+xml');
    $channel->appendChild($self);
    $channel->appendChild($page->createElement('generator','Idno http://idno.co'));

    // In case this isn't a feed page, find any objects
    if (empty($vars['items']) && !empty($vars['object'])) {
        $vars['items'] = array($vars['object']);
    }

    // If we have a feed, add the items
    if (!empty($vars['items'])) {
        foreach($vars['items'] as $item) {
            if ($item instanceof \Idno\Entities\ActivityStreamPost) {
                $item = $item->getObject();
            }
            $rssItem = $page->createElement('item');
            $rssItem->appendChild($page->createElement('title',$item->getTitle()));
            $rssItem->appendChild($page->createElement('link',$item->getURL()));
            $rssItem->appendChild($page->createElement('guid',$item->getUUID()));
            $rssItem->appendChild($page->createElement('pubDate',date(DATE_RSS,$item->created)));
            $rssItem->appendChild($page->createElement('description',$item->draw()));
            $channel->appendChild($rssItem);
        }
    }

    $rss->appendChild($channel);
    $page->appendChild($rss);
    echo $page->saveXML();