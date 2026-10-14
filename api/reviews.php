<?php
/**
 * API REST: Calificaciones y Reseñas (Reviews)
 * Endpoint: POST
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no soportado']);
    exit;
}

try {
    $pdo = Database::getConnection();

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $requestId = $input['requestId'] ?? null;
    $workerId = $input['workerId'] ?? null;
    $rating = (int)($input['rating'] ?? 5);
    $comment = trim($input['comment'] ?? '');
    $clientName = $input['clientName'] ?? 'Usuario Verificado';
    $clientAvatar = $input['clientAvatar'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80';
    $serviceTitle = $input['serviceTitle'] ?? 'Servicio Técnico';

    if (!$workerId || $rating < 1 || $rating > 5) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'workerId y rating válido (1-5) son requeridos']);
        exit;
    }

    $revId = 'rev-' . round(microtime(true) * 1000);
    $reviewDate = 'Hoy';

    // 1. Insertar reseña
    $stmt = $pdo->prepare("INSERT INTO `reviews` (`id`, `worker_id`, `request_id`, `client_name`, `client_avatar`, `rating`, `comment`, `review_date`, `service_title`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$revId, $workerId, $requestId, $clientName, $clientAvatar, $rating, $comment, $reviewDate, $serviceTitle]);

    // 2. Marcar solicitud con reseña si aplica
    if ($requestId) {
        $stmtReq = $pdo->prepare("UPDATE `service_requests` SET `has_review` = 1 WHERE `id` = ?");
        $stmtReq->execute([$requestId]);
    }

    // 3. Recalcular promedio de calificación del trabajador
    $stmtCalc = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM `reviews` WHERE `worker_id` = ?");
    $stmtCalc->execute([$workerId]);
    $calc = $stmtCalc->fetch();
    $avgRating = round((float)$calc['avg_rating'], 2);
    $totalReviews = (int)$calc['total'];

    $stmtUpd = $pdo->prepare("UPDATE `workers` SET `rating` = ?, `total_reviews` = ? WHERE `id` = ?");
    $stmtUpd->execute([$avgRating, $totalReviews, $workerId]);

    // 4. Registro de auditoría
    $audId = 'aud-' . round(microtime(true) * 1000);
    $stmtAud = $pdo->prepare("INSERT INTO `audit_logs` (`id`, `action`, `user`, `timestamp`, `schema_name`, `details`, `severity`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtAud->execute([$audId, 'CALIFICACION_REGISTRADA', 'usuario_cliente', date('Y-m-d H:i:s'), 'schema_reputacion', "Calificación de {$rating} estrellas registrada para trabajador ID: {$workerId}. Nuevo promedio: {$avgRating}.", 'INFO']);

    echo json_encode([
        'success' => true,
        'message' => '¡Calificación guardada exitosamente!',
        'review' => [
            'id' => $revId,
            'workerId' => $workerId,
            'rating' => $rating,
            'comment' => $comment,
            'avgRating' => $avgRating,
            'totalReviews' => $totalReviews
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
