<?php
namespace App\Domain\Event;

interface DomainEventInterface {
    public function getEventId(): string;

    public function getEventName(): string;

    public function getOccurredOn(): string;

    public function getPayload(): array;
}
