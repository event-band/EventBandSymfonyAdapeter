<?php
/**
 * @LICENSE_TEXT
 */

namespace EventBand\Adapter\Symfony;

use EventBand\BandDispatcher;
use EventBand\Event;
use EventBand\Subscription;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class BandEventDispatcher
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class BandEventDispatcher implements BandDispatcher
{
    const DEFAULT_BAND_PREFIX = '__event_band__';

    private $eventDispatcher;
    private $bandPrefix;
    private $subscriptions;

    public function __construct(EventDispatcherInterface $eventDispatcher, $bandPrefix = self::DEFAULT_BAND_PREFIX)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->bandPrefix = $bandPrefix;
        $this->subscriptions = new \SplObjectStorage();
    }

    /**
     * {@inheritDoc}
     */
    public function dispatchEvent(Event $event, $band = null)
    {
        $eventName = $this->getBandEventName($event->getName(), $band);

        $symfonyEvent = $event instanceof SymfonyEvent ? $event : new SymfonyEventWrapper($event);
        $this->eventDispatcher->dispatch($eventName, $symfonyEvent);

        return !$symfonyEvent->isPropagationStopped();
    }

    /**
     * {@inheritDoc}
     */
    public function subscribe(Subscription $subscription, $priority = 0)
    {
        if ($this->subscriptions->contains($subscription)) {
            return;
        }

        $eventName = $this->getSubscriptionName($subscription);

        $listener = new AdapterEventListener($this, $subscription);
        $this->subscriptions->attach($subscription, $listener);

        $this->eventDispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * @param Subscription $subscription
     */
    public function unsubscribe(Subscription $subscription)
    {
        if ($this->subscriptions->contains($subscription)) {
            $this->eventDispatcher->removeListener($this->getSubscriptionName($subscription),$this->subscriptions->offsetGet($subscription));
            $this->subscriptions->detach($subscription);
        }
    }

    private function getBandEventName($eventName, $band)
    {
        if (!empty($band)) {
            $eventName = sprintf('%s.%s.%s', $this->bandPrefix, $band, $eventName);
        }

        return $eventName;
    }

    private function getSubscriptionName(Subscription $subscription)
    {
        return $this->getBandEventName($subscription->getEventName(), $subscription->getBand());
    }
}