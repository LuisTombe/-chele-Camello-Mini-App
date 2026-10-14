<?php
namespace App\Domain\Observer;

use App\Domain\Event\DomainEventInterface;

interface EventSubscriberInterface {
    /**
     * Devuelve array asociativo de eventos suscritos:
     * ['evento.nombre' => 'metodoHandler']
     */
    public static function getSubscribedEvents(): array;
}
