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

    public function __construct(SymfonyEvent $symfonyEvent)
    {
        $this->symfonyEvent = $symfonyEvent;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->symfonyEvent->getName();
    }
}