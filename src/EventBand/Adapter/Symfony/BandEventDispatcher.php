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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\Exception\OutOfBoundsException;

/**
 * Class BandEventDispatcher
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class BandEventDispatcher implements EventDispatcherInterface, BandDispatcher
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
    public function dispatch($eventName, SymfonyEvent $event = null)
    {
        if ($event === null) {
            $event = new SymfonyBandEvent();
        }

        return $this->eventDispatcher->dispatch($eventName, $event);
    }

    /**
     * {@inheritDoc}
     */
    public function subscribe(Subscription $subscription, $priority = 0)
    {
        $this->attachListener($subscription, new AdapterEventListener($this, $subscription), $priority);
    }

    /**
     * {@inheritDoc}
     */
    public function addListener($eventName, $listener, $priority = 0)
    {
        $this->attachListener(new ListenerSubscription($eventName, $listener, $this->eventDispatcher), $listener, $priority);
    }

    /**
     * {@inheritDoc}
     */
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        // Copy-Pasted from EventDispatcher::addSubscriber() to ensure addListener() call
        foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
            if (is_string($params)) {
                $this->addListener($eventName, array($subscriber, $params));
            } elseif (is_string($params[0])) {
                $this->addListener($eventName, array($subscriber, $params[0]), isset($params[1]) ? $params[1] : 0);
            } else {
                foreach ($params as $listener) {
                    $this->addListener($eventName, array($subscriber, $listener[0]), isset($listener[1]) ? $listener[1] : 0);
                }
            }
        }
    }

    protected function attachListener(Subscription $subscription, callable $listener, $priority)
    {
        $this->subscriptions->attach($subscription, [$listener, $priority]);
        $this->eventDispatcher->addListener($this->getSubscriptionEventName($subscription), $listener, $priority);
    }

    /**
     * @param Subscription $subscription
     */
    public function unsubscribe(Subscription $subscription)
    {
        if ($this->subscriptions->contains($subscription)) {
            $this->eventDispatcher->removeListener($this->getSubscriptionEventName($subscription),$this->subscriptions->offsetGet($subscription)[0]);
            $this->subscriptions->detach($subscription);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function removeListener($eventName, $listener)
    {
        if ($subscription = $this->findListenerSubscription($listener)) {
            $this->unsubscribe($subscription);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function removeSubscriber(EventSubscriberInterface $subscriber)
    {
        // Copy-Pasted from EventDispatcher::removeSubscriber() to ensure removeListener() call
        foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
            if (is_array($params) && is_array($params[0])) {
                foreach ($params as $listener) {
                    $this->removeListener($eventName, array($subscriber, $listener[0]));
                }
            } else {
                $this->removeListener($eventName, array($subscriber, is_string($params) ? $params : $params[0]));
            }
        }
    }

    /**
     * @param callable $listener
     *
     * @return Subscription|null
     */
    protected function findListenerSubscription(callable $listener)
    {
        foreach ($this->subscriptions as $subscription => $listenerData) {
            if ($listenerData[0] === $listener) {
                return $subscription;
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscriptions()
    {
        return new \ArrayIterator(iterator_to_array($this->subscriptions));
    }

    /**
     * {@inheritDoc}
     */
    public function getListeners($eventName = null)
    {
        // TODO: sync with internal
        return $this->eventDispatcher->getListeners($eventName);
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscriptionPriority(Subscription $subscription)
    {
        // TODO: sync with internal
        if (!$this->subscriptions->contains($subscription)) {
            throw new OutOfBoundsException('Subscription does not exists');
        }

        return $this->subscriptions->offsetGet($subscription)[1];
    }

    /**
     * {@inheritDoc}
     */
    public function hasListeners($eventName = null)
    {
        return $this->eventDispatcher->hasListeners($eventName);
    }

    private function getBandEventName($eventName, $band)
    {
        if (!empty($band)) {
            $eventName = sprintf('%s.%s.%s', $this->bandPrefix, $band, $eventName);
        }

        return $eventName;
    }

    private function getSubscriptionEventName(Subscription $subscription)
    {
        return $this->getBandEventName($subscription->getEventName(), $subscription->getBand());
    }
}