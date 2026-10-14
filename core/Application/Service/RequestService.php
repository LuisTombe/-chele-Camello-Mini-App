<?php
namespace App\Application\Service;

use App\Domain\Entity\ServiceRequest;
use App\Domain\Event\SolicitudCreadaEvent;
use App\Domain\Event\SolicitudAceptadaEvent;
use App\Domain\Event\SolicitudRechazadaEvent;
use App\Domain\Event\SolicitudProgramadaEvent;
use App\Domain\Event\SolicitudEnProcesoEvent;
use App\Domain\Event\SolicitudFinalizadaEvent;
use App\Domain\Event\SolicitudCanceladaEvent;
use App\Domain\Exception\EntityNotFoundException;
use App\Domain\Exception\ValidationException;
use App\Domain\Observer\EventDispatcher;
use App\Application\DTO\CreateServiceRequestDTO;
use App\Application\DTO\ChangeRequestStatusDTO;
use App\Infrastructure\Repository\ServiceRequestRepository;
use App\Infrastructure\Repository\WorkerRepository;

class RequestService {
    private ServiceRequestRepository $requestRepo;
    private WorkerRepository $workerRepo;
    private EventDispatcher $dispatcher;

    public function __construct(
        ServiceRequestRepository $requestRepo,
        WorkerRepository $workerRepo,
        EventDispatcher $dispatcher
    ) {
        $this->requestRepo = $requestRepo;
        $this->workerRepo = $workerRepo;
        $this->dispatcher = $dispatcher;
    }

    public function createRequest(CreateServiceRequestDTO $dto): ServiceRequest {
        // 1. Validar que el trabajador exista
        $worker = $this->workerRepo->findById($dto->workerId);
        if (!$worker) {
            throw new EntityNotFoundException('Trabajador', $dto->workerId);
        }

        // 2. Control de concurrencia y agenda: verificar disponibilidad en el horario solicitado
        if (!$this->requestRepo->isWorkerAvailableAt($dto->workerId, $dto->scheduledDate, $dto->scheduledTime)) {
            throw new ValidationException([
                "El trabajador {$worker->getName()} ya tiene un servicio programado para el {$dto->scheduledDate} a las {$dto->scheduledTime}."
            ]);
        }

        // 3. Crear entidad con ID y Código únicos
        $newId = 'req-' . round(microtime(true) * 1000) . '-' . bin2hex(random_bytes(2));
        $newCode = 'CAM-2026-' . rand(100, 999);
        $now = date('Y-m-d H:i');

        $request = new ServiceRequest(
            $newId,
            $newCode,
            $dto->clientId,
            $dto->clientName,
            $dto->workerId,
            $dto->workerName ?: $worker->getName(),
            $dto->serviceTitle,
            $dto->scheduledDate,
            $dto->scheduledTime,
            $dto->address,
            $dto->neighborhood,
            $dto->description,
            $dto->estimatedCost,
            $dto->urgent,
            $dto->category,
            $dto->clientPhone,
            $dto->clientAvatar,
            $dto->workerSpecialty ?: $worker->getSpecialty(),
            $dto->workerAvatar ?: $worker->getAvatar(),
            $dto->diagnosisPhoto,
            false,
            null,
            $now,
            $now
        );

        // 4. Persistir
        $this->requestRepo->save($request);

        // 5. Emitir evento de dominio (notificación bot y auditoría automáticas)
        $this->dispatcher->dispatch(new SolicitudCreadaEvent($request->toArray()));

        return $request;
    }

    public function changeStatus(ChangeRequestStatusDTO $dto): ServiceRequest {
        // 1. Cargar solicitud existente
        $request = $this->requestRepo->findById($dto->requestId);
        if (!$request) {
            throw new EntityNotFoundException('Solicitud de servicio', $dto->requestId);
        }

        $oldStatus = $request->getStatus();
        $target = $dto->targetStatus;

        // 2. Ejecutar transición mediante State Pattern
        switch ($target) {
            case 'ACEPTADA':
                $request->accept($dto->actorRole, $dto->actorId);
                $this->requestRepo->updateStatusAndSchedule($request);
                $this->dispatcher->dispatch(new SolicitudAceptadaEvent([
                    'requestId' => $request->getId(),
                    'code' => $request->getCode(),
                    'status' => 'ACEPTADA',
                    'actor' => $dto->actorRole === 'TRABAJADOR' ? 'tecnico_camellador' : 'usuario_cliente'
                ]));
                break;

            case 'RECHAZADA':
                $request->reject($dto->actorRole, $dto->actorId, $dto->reason ?? '');
                $this->requestRepo->updateStatusAndSchedule($request);
                $this->dispatcher->dispatch(new SolicitudRechazadaEvent([
                    'requestId' => $request->getId(),
                    'code' => $request->getCode(),
                    'status' => 'RECHAZADA',
                    'reason' => $dto->reason,
                    'actor' => 'tecnico_camellador'
                ]));
                break;

            case 'PROGRAMADA':
                $date = $dto->scheduledDate ?? $request->getScheduledDate();
                $time = $dto->scheduledTime ?? $request->getScheduledTime();

                // Validación de concurrencia: evitar solapamiento
                if (!$this->requestRepo->isWorkerAvailableAt($request->getWorkerId(), $date, $time, $request->getId())) {
                    throw new ValidationException(["El trabajador ya tiene otro servicio confirmado en el horario {$date} {$time}."]);
                }

                $request->schedule($dto->actorRole, $dto->actorId, $date, $time);
                $this->requestRepo->updateStatusAndSchedule($request);
                $this->dispatcher->dispatch(new SolicitudProgramadaEvent([
                    'requestId' => $request->getId(),
                    'code' => $request->getCode(),
                    'status' => 'PROGRAMADA',
                    'date' => $date,
                    'time' => $time,
                    'actor' => $dto->actorRole === 'TRABAJADOR' ? 'tecnico_camellador' : 'usuario_cliente'
                ]));
                break;

            case 'EN_PROCESO':
                $request->start($dto->actorRole, $dto->actorId);
                $this->requestRepo->updateStatusAndSchedule($request);
                $this->dispatcher->dispatch(new SolicitudEnProcesoEvent([
                    'requestId' => $request->getId(),
                    'code' => $request->getCode(),
                    'status' => 'EN_PROCESO',
                    'actor' => 'tecnico_camellador'
                ]));
                break;

            case 'FINALIZADA':
                $request->finish($dto->actorRole, $dto->actorId);
                $this->requestRepo->updateStatusAndSchedule($request);
                // Incrementar contador de trabajos completados del trabajador
                $this->workerRepo->incrementCompletedJobs($request->getWorkerId());
                $this->dispatcher->dispatch(new SolicitudFinalizadaEvent([
                    'requestId' => $request->getId(),
                    'code' => $request->getCode(),
                    'status' => 'FINALIZADA',
                    'actor' => 'tecnico_camellador'
                ]));
                break;

            case 'CANCELADA':
                $request->cancel($dto->actorRole, $dto->actorId, $dto->reason ?? '');
                $this->requestRepo->updateStatusAndSchedule($request);
                $this->dispatcher->dispatch(new SolicitudCanceladaEvent([
                    'requestId' => $request->getId(),
                    'code' => $request->getCode(),
                    'status' => 'CANCELADA',
                    'reason' => $dto->reason,
                    'actor' => $dto->actorRole === 'TRABAJADOR' ? 'tecnico_camellador' : 'usuario_cliente'
                ]));
                break;

            default:
                throw new ValidationException(["Acción o estado '{$target}' no reconocido."]);
        }

        return $request;
    }

    public function getRequests(array $filters = []): array {
        return $this->requestRepo->findByFilters($filters);
    }

    public function getRequestById(string $id): ServiceRequest {
        $req = $this->requestRepo->findById($id);
        if (!$req) {
            throw new EntityNotFoundException('Solicitud de servicio', $id);
        }
        return $req;
    }
}
