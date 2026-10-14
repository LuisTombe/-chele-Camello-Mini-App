<?php
namespace App\Domain\State\Report;

use App\Domain\Entity\Report;

interface ReportStateInterface {
    public function getStatus(): string;

    public function startReview(Report $report, string $adminUser): void;

    public function resolve(Report $report, string $adminUser, string $resolutionNotes): void;

    public function dismiss(Report $report, string $adminUser, string $reason): void;
}
