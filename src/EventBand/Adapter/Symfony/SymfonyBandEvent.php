<?php
/**
 * @LICENSE_TEXT
 */

namespace EventBand\Adapter\Symfony;

use EventBand\Event;

/**
 * Class BandEvent
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class SymfonyBandEvent extends SerializableSymfonyEvent implements Event {}