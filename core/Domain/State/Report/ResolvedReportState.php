<?php
namespace App\Domain\State\Report;

use App\Domain\Entity\Report;
use App\Domain\Exception\InvalidStateTransitionException;

class ResolvedReportState implements ReportStateInterface {
    public function getStatus(): string {
        return 'RESUELTO';
    }

    public function startReview(Report $report, string $adminUser): void {
        throw new InvalidStateTransitionException('RESUELTO', 'EN_REVISION', 'El reporte ya ha sido resuelto formalmente.');
    }

    public function resolve(Report $report, string $adminUser, string $resolutionNotes): void {
        throw new InvalidStateTransitionException('RESUELTO', 'RESUELTO', 'El reporte ya está resuelto.');
    }

    public function dismiss(Report $report, string $adminUser, string $reason): void {
        throw new InvalidStateTransitionException('RESUELTO', 'DESCARTADO', 'Un reporte resuelto no puede ser descartado.');
    }
}
