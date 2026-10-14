<?php
namespace App\Application\Service;

use App\Domain\Entity\Report;
use App\Domain\Event\ModeracionResueltaEvent;
use App\Domain\Exception\EntityNotFoundException;
use App\Domain\Observer\EventDispatcher;
use App\Application\DTO\CreateReportDTO;
use App\Application\DTO\ResolveReportDTO;
use App\Infrastructure\Repository\ReportRepository;

class ReportService {
    private ReportRepository $reportRepo;
    private EventDispatcher $dispatcher;

    public function __construct(ReportRepository $reportRepo, EventDispatcher $dispatcher) {
        $this->reportRepo = $reportRepo;
        $this->dispatcher = $dispatcher;
    }

    public function createReport(CreateReportDTO $dto): Report {
        $newId = 'rep-' . round(microtime(true) * 1000) . '-' . bin2hex(random_bytes(2));
        $report = new Report(
            $newId,
            $dto->reporterId,
            $dto->reporterRole,
            $dto->targetType,
            $dto->targetId,
            $dto->reason,
            $dto->description,
            $dto->requestId
        );

        $this->reportRepo->save($report);
        return $report;
    }

    public function resolveReport(ResolveReportDTO $dto): Report {
        $report = $this->reportRepo->findById($dto->reportId);
        if (!$report) {
            throw new EntityNotFoundException('Reporte de moderación', $dto->reportId);
        }

        switch ($dto->decision) {
            case 'EN_REVISION':
                $report->startReview($dto->adminUser);
                break;
            case 'RESUELTO':
                $report->resolve($dto->adminUser, $dto->resolutionNotes);
                $this->dispatcher->dispatch(new ModeracionResueltaEvent([
                    'reportId' => $report->getId(),
                    'status' => 'RESUELTO',
                    'targetType' => $report->getTargetType(),
                    'targetId' => $report->getTargetId(),
                    'adminUser' => $dto->adminUser
                ]));
                break;
            case 'DESCARTADO':
                $report->dismiss($dto->adminUser, $dto->resolutionNotes);
                break;
        }

        $this->reportRepo->save($report);
        return $report;
    }

    public function getReports(array $filters = []): array {
        return $this->reportRepo->findAll($filters);
    }
}
