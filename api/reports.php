<?php
/**
 * API REST: Reportes y Moderación Administrativa
 * Implementa State, Repository, DTO y Eventos de Dominio
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../core/bootstrap.php';

use App\Application\DTO\CreateReportDTO;
use App\Application\DTO\ResolveReportDTO;
use App\Domain\Exception\ValidationException;
use App\Domain\Exception\InvalidStateTransitionException;
use App\Domain\Exception\EntityNotFoundException;

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $container = getAppContainer();
    $reportService = $container->getReportService();

    if ($method === 'GET') {
        $filters = [
            'status' => $_GET['status'] ?? null,
            'targetType' => $_GET['targetType'] ?? null,
        ];
        $reports = $reportService->getReports($filters);
        $data = array_map(fn($r) => $r->toArray(), $reports);

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    if ($method === 'POST' && $action === 'resolve') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $dto = new ResolveReportDTO($input);
        $report = $reportService->resolveReport($dto);

        echo json_encode(['success' => true, 'message' => "Reporte resuelto con estado {$report->getStatus()}", 'report' => $report->toArray()]);
        exit;
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $dto = new CreateReportDTO($input);
        $report = $reportService->createReport($dto);

        echo json_encode(['success' => true, 'message' => 'Reporte registrado exitosamente para revisión de moderación', 'report' => $report->toArray()]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no soportado']);

} catch (ValidationException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'errors' => $e->getErrors()]);
} catch (InvalidStateTransitionException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (EntityNotFoundException $e) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
