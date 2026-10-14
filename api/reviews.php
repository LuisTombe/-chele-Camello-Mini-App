<?php
/**
 * API REST: Calificaciones y Reseñas (Reviews)
 * Refactorizado con Patrón Observer, Repository y DTO
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../core/bootstrap.php';

use App\Application\DTO\CreateReviewDTO;
use App\Domain\Exception\ValidationException;
use App\Domain\Exception\EntityNotFoundException;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no soportado']);
    exit;
}

try {
    $container = getAppContainer();
    $reviewService = $container->getReviewService();

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $dto = new CreateReviewDTO($input);
    $review = $reviewService->createReview($dto);

    // Obtener datos actualizados del trabajador mediante repositorio
    $workerRepo = $container->getWorkerRepository();
    $worker = $workerRepo->findById($dto->workerId);

    echo json_encode([
        'success' => true,
        'message' => '¡Calificación guardada exitosamente!',
        'review' => [
            'id' => $review->getId(),
            'workerId' => $review->getWorkerId(),
            'rating' => $review->getRating(),
            'comment' => $review->getComment(),
            'avgRating' => $worker ? $worker->getRating() : 5.0,
            'totalReviews' => $worker ? $worker->getTotalReviews() : 1
        ]
    ]);

} catch (ValidationException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'errors' => $e->getErrors()]);
} catch (EntityNotFoundException $e) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
