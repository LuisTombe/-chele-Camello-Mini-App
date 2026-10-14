<?php
namespace App\Domain\State\Report;

use App\Domain\Entity\Report;

class OpenReportState implements ReportStateInterface {
    public function getStatus(): string {
        return 'ABIERTO';
    }

    public function startReview(Report $report, string $adminUser): void {
        $report->setResolvedBy($adminUser);
        $report->transitionTo(new UnderReviewReportState());
    }

    public function resolve(Report $report, string $adminUser, string $resolutionNotes): void {
        $report->setResolutionNotes($resolutionNotes);
        $report->setResolvedBy($adminUser);
        $report->transitionTo(new ResolvedReportState());
    }

    public function dismiss(Report $report, string $adminUser, string $reason): void {
        $report->setResolutionNotes($reason);
        $report->setResolvedBy($adminUser);
        $report->transitionTo(new DismissedReportState());
    }
}
