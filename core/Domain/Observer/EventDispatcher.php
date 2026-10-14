<?php
namespace App\Domain\Observer;

use App\Domain\Event\DomainEventInterface;

class EventDispatcher {
    /** @var array<string, array<callable>> */
    private array $listeners = [];

    public function addListener(string $eventName, callable $listener): void {
        $this->listeners[$eventName][] = $listener;
    }

    public function addSubscriber(EventSubscriberInterface $subscriber): void {
        foreach ($subscriber::getSubscribedEvents() as $eventName => $method) {
            $this->addListener($eventName, [$subscriber, $method]);
        }
    }

    public function dispatch(DomainEventInterface $event): void {
        $eventName = $event->getEventName();
        if (empty($this->listeners[$eventName])) {
            return;
        }

        foreach ($this->listeners[$eventName] as $listener) {
            try {
                call_user_func($listener, $event);
            } catch (\Throwable $t) {
                // Registrar error sin romper el flujo principal si es un efecto no crítico
                error_log("Error en listener de evento '{$eventName}': " . $t->getMessage());
            }
        }
    }
}
