<?php
/**
 * @LICENSE_TEXT
 */

namespace EventBand\Adapter\Symfony\Tests;

use EventBand\Adapter\Symfony\BandEventDispatcher;
use EventBand\Adapter\Symfony\SymfonyEventWrapper;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Class BandEventDispatcherTest
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class BandEventDispatcherTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eventDispatcher;
    /**
     * @var BandEventDispatcher
     */
    private $bandDispatcher;

    protected function setUp()
    {
        $this->eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->bandDispatcher = new BandEventDispatcher($this->eventDispatcher, '~prefix~');
    }

    /**
     * @test band dispatch will delegate dispatching to internal event dispatcher
     */
    public function dispatchEventWithInternal()
    {
        $event = $this->getMock('EventBand\Event');
        $event
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('event.name'))
        ;

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with('event.name', $this->callback(function (SymfonyEventWrapper $wrapper) use ($event) {
                return $event === $wrapper->getWrappedEvent();
            }))
        ;

        $this->assertTrue($this->bandDispatcher->dispatchEvent($event));
    }

    /**
     * @test band dispatch will prefix event
     */
    public function dispatchEventWithBand()
    {
        $event = $this->getMock('EventBand\Event');
        $event
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('event.name'))
        ;

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with('~prefix~.band_name.event.name', $this->anything())
        ;

        $this->bandDispatcher->dispatchEvent($event, 'band_name');
    }

    /**
     * @test if propagation was stopped dispatch will return false
     */
    public function dispatchEventPropagation()
    {
        $event = $this->getMock('EventBand\Event');
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->will($this->returnCallback(function ($eventName, SymfonyEventWrapper $wrapper) {
                $wrapper->stopPropagation();
            }))
        ;

        $this->assertFalse($this->bandDispatcher->dispatchEvent($event));
    }

    /**
     * @test subscribe add new adapter listener
     */
    public function addAdapterListener()
    {
        $subscription = $this->getMock('EventBand\Subscription');
        $subscription
            ->expects($this->any())
            ->method('getEventName')
            ->will($this->returnValue('event.name'))
        ;
        $subscription
            ->expects($this->any())
            ->method('getBand')
            ->will($this->returnValue('band_name'))
        ;

        $this->eventDispatcher
            ->expects($this->once())
            ->method('addListener')
            ->with(
                '~prefix~.band_name.event.name',
                $this->isInstanceOf('EventBand\Adapter\Symfony\AdapterEventListener'),
                10
            );

        $this->bandDispatcher->subscribe($subscription, 10);
    }

    /**
     * @test unsubscribe remove related adapter listener
     */
    public function removeWrapperListener()
    {
        $subscription = $this->getMock('EventBand\Subscription');
        $subscription
            ->expects($this->any())
            ->method('getEventName')
            ->will($this->returnValue('event.name'))
        ;
        $subscription
            ->expects($this->any())
            ->method('getBand')
            ->will($this->returnValue('band_name'))
        ;

        $this->eventDispatcher
            ->expects($this->once())
            ->method('removeListener')
            ->with(
                '~prefix~.band_name.event.name',
                $this->isInstanceOf('EventBand\Adapter\Symfony\AdapterEventListener')
            );

        $this->bandDispatcher->subscribe($subscription, 10);
        $this->bandDispatcher->unsubscribe($subscription);
    }
}
