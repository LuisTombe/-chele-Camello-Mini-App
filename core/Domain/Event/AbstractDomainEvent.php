<?php
namespace App\Domain\Event;

abstract class AbstractDomainEvent implements DomainEventInterface {
    protected string $eventId;
    protected string $occurredOn;
    protected array $payload;

    public function __construct(array $payload = [], ?string $eventId = null) {
        $this->eventId = $eventId ?? ('evt-' . round(microtime(true) * 1000) . '-' . bin2hex(random_bytes(4)));
        $this->occurredOn = date('Y-m-d H:i:s');
        $this->payload = $payload;
    }

    public function getEventId(): string {
        return $this->eventId;
    }

    abstract public function getEventName(): string;

    public function getOccurredOn(): string {
        return $this->occurredOn;
    }

    public function getPayload(): array {
        return $this->payload;
    }
}
