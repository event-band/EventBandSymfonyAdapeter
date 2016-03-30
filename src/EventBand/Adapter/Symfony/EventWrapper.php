<?php
/**
 * @LICENSE_TEXT
 */

namespace EventBand\Adapter\Symfony;

use EventBand\Event;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

/**
 * Class EventWrapper
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class EventWrapper implements Event
{
    private $symfonyEvent;
    private $name;

    /**
     * Wrap symfony event
     *
     * @param SymfonyEvent  $symfonyEvent
     * @param string|null   $name
     */
    public function __construct(SymfonyEvent $symfonyEvent, $name = null)
    {
        $this->symfonyEvent = $symfonyEvent;
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name !== null ? $this->name : $this->symfonyEvent->getName();
    }
}
