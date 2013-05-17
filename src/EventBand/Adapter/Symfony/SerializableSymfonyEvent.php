<?php
/**
 * @LICENSE_TEXT
 */

namespace EventBand\Adapter\Symfony;

use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

/**
 * Class SeriazlizableEvent
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class SerializableSymfonyEvent extends SymfonyEvent implements \Serializable
{
    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        return serialize($this->toSerializableArray());
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        if (!is_array($data)) {
            throw new \RuntimeException(sprintf('Unserialized data is not an array but "%s"', $data));
        }
    }

    protected function toSerializableArray()
    {
        return [
            'name' => $this->getName()
        ];
    }

    protected function fromUnserializedArray(array $data)
    {
        if (isset($data['name'])) {
            throw new \RuntimeException('Key "name" is not set in unserialized array');
        }

        $this->setName($data['name']);
    }
}