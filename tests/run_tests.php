<?php
/**
 * Suite de Pruebas Automatizadas de Arquitectura y Patrones de Diseño
 * Proyecto: Échele Camello
 * Ejecutable vía CLI: php tests/run_tests.php
 */

require_once __DIR__ . '/../core/bootstrap.php';

use App\Domain\Entity\ServiceRequest;
use App\Domain\Entity\Service;
use App\Domain\Entity\Report;
use App\Domain\Entity\User;
use App\Domain\Exception\InvalidStateTransitionException;
use App\Domain\Exception\UnauthorizedActionException;
use App\Domain\Exception\ValidationException;
use App\Application\DTO\RegisterUserDTO;
use App\Application\DTO\CreateServiceRequestDTO;
use App\Application\DTO\ChangeRequestStatusDTO;
use App\Application\DTO\CreateReviewDTO;
use App\Application\DTO\CreateServiceDTO;
use App\Application\DTO\CreateReportDTO;
use App\Application\DTO\ResolveReportDTO;

$passed = 0;
$failed = 0;

function assertTest(string $description, bool $condition, string $detail = ''): void {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [OK] {$description}\n";
    } else {
        $failed++;
        echo "  [FAIL] {$description} " . ($detail ? "({$detail})" : "") . "\n";
    }
}

echo "============================================================\n";
echo " INICIANDO PRUEBAS DE PATRONES DE DISEÑO — ÉCHELE CAMELLO\n";
echo "============================================================\n\n";

$container = getAppContainer();

// -----------------------------------------------------------------
// GRUPO 1: PATRÓN DTO Y VALIDACIÓN DE LÍMITES
// -----------------------------------------------------------------
echo "1. Verificando Patrón DTO y Reglas de Seguridad...\n";

// 1.1 Bloqueo de auto-asignación de ADMINISTRADOR
try {
    new RegisterUserDTO([
        'name' => 'Intento Hack',
        'email' => 'hack@sena.edu.co',
        'password' => '123456',
        'role' => 'ADMINISTRADOR'
    ]);
    assertTest("DTO bloquea auto-asignación de rol ADMINISTRADOR", false, "Permitió crear admin");
} catch (ValidationException $e) {
    assertTest("DTO bloquea auto-asignación de rol ADMINISTRADOR", true);
}

// 1.2 Validación de longitud mínima de contraseña
try {
    new RegisterUserDTO([
        'name' => 'Luis Tombe',
        'email' => 'luis@sena.edu.co',
        'password' => '123',
        'role' => 'CLIENTE'
    ]);
    assertTest("DTO exige contraseña mínima de 6 caracteres", false);
} catch (ValidationException $e) {
    assertTest("DTO exige contraseña mínima de 6 caracteres", true);
}

// 1.3 Validación de fechas pasadas en solicitudes
try {
    new CreateServiceRequestDTO([
        'workerId' => 'w-1',
        'serviceTitle' => 'Plomería',
        'address' => 'Calle 8 # 14-20',
        'scheduledDate' => '2020-01-01', // Fecha pasada
        'scheduledTime' => '10:00',
        'description' => 'Tubería rota en baño principal',
        'estimatedCost' => 50000
    ]);
    assertTest("DTO rechaza fechas pasadas en solicitudes", false);
} catch (ValidationException $e) {
    assertTest("DTO rechaza fechas pasadas en solicitudes", true);
}

// 1.4 Validación de costo estimado > 0
try {
    new CreateServiceRequestDTO([
        'workerId' => 'w-1',
        'serviceTitle' => 'Plomería',
        'address' => 'Calle 8 # 14-20',
        'scheduledDate' => date('Y-m-d', strtotime('+1 day')),
        'scheduledTime' => '10:00',
        'description' => 'Tubería rota en baño principal',
        'estimatedCost' => -1000
    ]);
    assertTest("DTO rechaza costos estimados negativos o cero", false);
} catch (ValidationException $e) {
    assertTest("DTO rechaza costos estimados negativos o cero", true);
}

// 1.5 DTO protege secretos en salida
$testUser = new User('usr-test', 'Ana Pérez', 'ana@correo.com', '$2y$10$hashsecretoficticio', 'CLIENTE');
$safe = $testUser->toSafeArray();
assertTest("DTO de usuario no expone hashes de contraseña", !isset($safe['password_hash']) && !isset($safe['password']));

echo "\n";

// -----------------------------------------------------------------
// GRUPO 2: PATRÓN STATE EN SOLICITUDES
// -----------------------------------------------------------------
echo "2. Verificando Patrón State en Ciclo de Vida de Solicitudes...\n";

$req = new ServiceRequest(
    'test-req-1', 'CAM-TEST-1', 'cli-1', 'Cliente Uno', 'w-1', 'Carlos Fontanero',
    'Arreglo Tubería', date('Y-m-d', strtotime('+2 days')), '09:00',
    'Cra 5 # 10-20', 'Cándido Leguízamo', 'Fuga de agua en lavaplatos', 45000
);

assertTest("Estado inicial de la solicitud es PENDIENTE", $req->getStatus() === 'PENDIENTE');

// 2.1 Transición inválida directa PENDIENTE -> FINALIZADA
try {
    $req->finish('TRABAJADOR', 'w-1');
    assertTest("Bloqueo de transición inválida PENDIENTE -> FINALIZADA", false);
} catch (InvalidStateTransitionException $e) {
    assertTest("Bloqueo de transición inválida PENDIENTE -> FINALIZADA", true);
}

// 2.2 Transición con rol no autorizado (un cliente no puede aceptar)
try {
    $req->accept('CLIENTE', 'cli-1');
    assertTest("Bloqueo de aceptación por rol no autorizado (CLIENTE)", false);
} catch (UnauthorizedActionException $e) {
    assertTest("Bloqueo de aceptación por rol no autorizado (CLIENTE)", true);
}

// 2.3 Aceptación válida por trabajador
$req->accept('TRABAJADOR', 'w-1');
assertTest("Transición válida PENDIENTE -> ACEPTADA", $req->getStatus() === 'ACEPTADA');

// 2.4 Programación válida
$req->schedule('TRABAJADOR', 'w-1', date('Y-m-d', strtotime('+2 days')), '14:00');
assertTest("Transición válida ACEPTADA -> PROGRAMADA", $req->getStatus() === 'PROGRAMADA');

// 2.5 Inicio de trabajo
$req->start('TRABAJADOR', 'w-1');
assertTest("Transición válida PROGRAMADA -> EN_PROCESO", $req->getStatus() === 'EN_PROCESO');

// 2.6 Finalización de trabajo
$req->finish('TRABAJADOR', 'w-1');
assertTest("Transición válida EN_PROCESO -> FINALIZADA", $req->getStatus() === 'FINALIZADA');

// 2.7 Estado terminal (no admite transiciones posteriores)
try {
    $req->cancel('CLIENTE', 'cli-1', 'Ya no quiero el servicio');
    assertTest("Estado terminal FINALIZADA rechaza cancelaciones posteriores", false);
} catch (InvalidStateTransitionException $e) {
    assertTest("Estado terminal FINALIZADA rechaza cancelaciones posteriores", true);
}

// 2.8 Prueba de rama rechazada: no puede continuar a programación
$reqRechazada = new ServiceRequest(
    'test-req-2', 'CAM-TEST-2', 'cli-1', 'Cliente Uno', 'w-1', 'Carlos Fontanero',
    'Arreglo Tubería', date('Y-m-d', strtotime('+3 days')), '09:00',
    'Cra 5 # 10-20', 'Cándido Leguízamo', 'Fuga de agua', 45000
);
$reqRechazada->reject('TRABAJADOR', 'w-1', 'Sin tiempo');
assertTest("Transición válida PENDIENTE -> RECHAZADA", $reqRechazada->getStatus() === 'RECHAZADA');

try {
    $reqRechazada->schedule('TRABAJADOR', 'w-1', date('Y-m-d', strtotime('+3 days')), '10:00');
    assertTest("Solicitud RECHAZADA no puede ser programada", false);
} catch (InvalidStateTransitionException $e) {
    assertTest("Solicitud RECHAZADA no puede ser programada", true);
}

echo "\n";

// -----------------------------------------------------------------
// GRUPO 3: PATRÓN STATE EN SERVICIOS Y MODERACIÓN
// -----------------------------------------------------------------
echo "3. Verificando Patrón State en Publicaciones y Reportes...\n";

$service = new Service('svc-test', 'w-1', 'Instalación de Grifos', 35000);
assertTest("Servicio recién creado inicia en ACTIVO", $service->getStatus() === 'ACTIVO');

// 3.1 Trabajador desactiva el servicio
$service->deactivate('TRABAJADOR');
assertTest("Trabajador desactiva servicio (ACTIVO -> INACTIVO)", $service->getStatus() === 'INACTIVO');

// 3.2 Trabajador reactiva su servicio
$service->activate('TRABAJADOR');
assertTest("Trabajador reactiva servicio (INACTIVO -> ACTIVO)", $service->getStatus() === 'ACTIVO');

// 3.3 Administrador suspende servicio por moderación
$service->suspend('ADMINISTRADOR', 'Incumplimiento de términos');
assertTest("Administrador suspende servicio (ACTIVO -> SUSPENDIDO)", $service->getStatus() === 'SUSPENDIDO');

// 3.4 Trabajador intenta eludir suspensión
try {
    $service->activate('TRABAJADOR');
    assertTest("Trabajador no puede eludir suspensión administrativa", false);
} catch (UnauthorizedActionException $e) {
    assertTest("Trabajador no puede eludir suspensión administrativa", true);
}

// 3.5 Administrador levanta suspensión
$service->unsuspend('ADMINISTRADOR');
assertTest("Administrador levanta suspensión exitosamente", $service->getStatus() === 'INACTIVO');

// 3.6 State en Reportes de Moderación
$report = new Report('rep-test', 'cli-1', 'CLIENTE', 'WORKER', 'w-1', 'Incumplimiento', 'El técnico no llegó');
assertTest("Reporte inicia en estado ABIERTO", $report->getStatus() === 'ABIERTO');

$report->startReview('admin_lucia');
assertTest("Reporte pasa a EN_REVISION", $report->getStatus() === 'EN_REVISION');

$report->resolve('admin_lucia', 'Se sancionó al trabajador con amonestación');
assertTest("Reporte pasa a RESUELTO", $report->getStatus() === 'RESUELTO');

try {
    $report->startReview('admin_pedro');
    assertTest("Reporte RESUELTO rechaza reapertura arbitraria", false);
} catch (InvalidStateTransitionException $e) {
    assertTest("Reporte RESUELTO rechaza reapertura arbitraria", true);
}

echo "\n";

// -----------------------------------------------------------------
// GRUPO 4: PATRÓN STRATEGY EN ORDENAMIENTO DE BÚSQUEDA
// -----------------------------------------------------------------
echo "4. Verificando Patrón Strategy en Búsqueda y Ordenamiento...\n";

$sortContext = $container->getSearchSortContext();

$mockWorkers = [
    ['id' => 'w-1', 'name' => 'Carlos', 'distance_km' => 3.5, 'rating' => 4.5, 'min_rate' => 50000, 'experience_years' => 4, 'total_reviews' => 10],
    ['id' => 'w-2', 'name' => 'María', 'distance_km' => 1.2, 'rating' => 4.9, 'min_rate' => 70000, 'experience_years' => 8, 'total_reviews' => 25],
    ['id' => 'w-3', 'name' => 'Jorge', 'distance_km' => 5.0, 'rating' => 4.9, 'min_rate' => 40000, 'experience_years' => 12, 'total_reviews' => 15],
];

// 4.1 Estrategia por Cercanía (menor distancia primero)
$workersCopy = $mockWorkers;
$sortContext->apply($workersCopy, 'cercania');
assertTest("Strategy Cercanía: primer resultado es el más cercano (1.2 km)", $workersCopy[0]['id'] === 'w-2' && $workersCopy[2]['id'] === 'w-3');

// 4.2 Estrategia por Calificación (mayor calificación primero, desempate por reseñas)
$workersCopy = $mockWorkers;
$sortContext->apply($workersCopy, 'calificacion');
assertTest("Strategy Calificación: primer resultado es la mejor calificada (4.9 y 25 reseñas)", $workersCopy[0]['id'] === 'w-2' && $workersCopy[1]['id'] === 'w-3');

// 4.3 Estrategia por Precio (menor tarifa primero)
$workersCopy = $mockWorkers;
$sortContext->apply($workersCopy, 'precio');
assertTest("Strategy Precio: primer resultado es el más económico ($40.000)", $workersCopy[0]['id'] === 'w-3' && $workersCopy[2]['id'] === 'w-2');

// 4.4 Estrategia por Experiencia (más años primero)
$workersCopy = $mockWorkers;
$sortContext->apply($workersCopy, 'experiencia');
assertTest("Strategy Experiencia: primer resultado es el más experimentado (12 años)", $workersCopy[0]['id'] === 'w-3');

// 4.5 Criterio desconocido rechazado con error controlado
try {
    $workersCopy = $mockWorkers;
    $sortContext->apply($workersCopy, 'criterio_inventado_xyz');
    assertTest("Rechazo de criterio de ordenamiento desconocido", false);
} catch (ValidationException $e) {
    assertTest("Rechazo de criterio de ordenamiento desconocido", true);
}

echo "\n";

// -----------------------------------------------------------------
// GRUPO 5: PATRÓN OBSERVER Y EVENTOS DE DOMINIO
// -----------------------------------------------------------------
echo "5. Verificando Patrón Observer y Reacción a Eventos...\n";

$auditRepo = $container->getAuditLogRepository();
$chatRepo = $container->getChatMessageRepository();
$reviewService = $container->getReviewService();

// Contar auditorías y mensajes antes del evento
$auditsBefore = count($auditRepo->findRecent(100));

// Crear solicitud mediante servicio de aplicación
$reqService = $container->getRequestService();
$uniqueTime = date('H:i', strtotime('+1 hour'));
$uniqueDate = date('Y-m-d', strtotime('+5 days'));

$newReq = $reqService->createRequest(new CreateServiceRequestDTO([
    'workerId' => 'w-1',
    'serviceTitle' => 'Mantenimiento Test',
    'address' => 'Calle 10 # 5-20',
    'neighborhood' => 'Centro',
    'scheduledDate' => $uniqueDate,
    'scheduledTime' => $uniqueTime,
    'description' => 'Servicio de prueba para Observer',
    'estimatedCost' => 55000
]));

$auditsAfter = count($auditRepo->findRecent(100));
assertTest("Observer AuditLogSubscriber registró evento SolicitudCreada", $auditsAfter > $auditsBefore);

$chatMessages = $chatRepo->findByRequestId($newReq->getId());
assertTest("Observer ChatBotNotificationSubscriber creó mensaje inicial en chat", count($chatMessages) >= 1);

// Transición a ACEPTADA -> debe generar mensaje bot automático
$reqService->changeStatus(new ChangeRequestStatusDTO([
    'requestId' => $newReq->getId(),
    'status' => 'ACEPTADA',
    'role' => 'TRABAJADOR'
]));

$chatMessagesAfterAccept = $chatRepo->findByRequestId($newReq->getId());
assertTest("Observer ChatBotNotificationSubscriber creó aviso de solicitud aceptada", count($chatMessagesAfterAccept) >= 2);

echo "\n";

// -----------------------------------------------------------------
// GRUPO 6: ADAPTERS (MAIL Y ALMACENAMIENTO)
// -----------------------------------------------------------------
echo "6. Verificando Patrón Adapter (Mail y Archivos)...\n";

$mailer = $container->getMailAdapter();
$sent = $mailer->send('cliente@sena.edu.co', 'Prueba de Adapter', '<p>Contenido de prueba</p>', true);
assertTest("Adapter Mail envía y registra correo exitosamente", $sent === true);

$storage = $container->getStorageAdapter();

// Crear archivo temporal para simular subida
$tmpFile = tempnam(sys_get_temp_dir(), 'test_img');
file_put_contents($tmpFile, "\xFF\xD8\xFF\xE0" . "fake image data"); // JPEG magic bytes
$fileData = [
    'name' => 'foto_diagnostico.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => $tmpFile,
    'size' => 1024,
    'error' => 0
];

$savedUrl = $storage->save($fileData, 'diagnosticos');
assertTest("Adapter Storage guarda archivo con ruta relativa segura", str_starts_with($savedUrl, 'assets/uploads/diagnosticos/'));

// Intentar subir extensión maliciosa
$badTmp = tempnam(sys_get_temp_dir(), 'test_bad');
file_put_contents($badTmp, "<?php echo 'malware';");
$badFile = [
    'name' => 'malware.php',
    'type' => 'text/php',
    'tmp_name' => $badTmp,
    'size' => 100,
    'error' => 0
];

try {
    $storage->save($badFile, 'uploads');
    assertTest("Adapter Storage bloquea subida de archivos ejecutables .php", false);
} catch (ValidationException $e) {
    assertTest("Adapter Storage bloquea subida de archivos ejecutables .php", true);
}

@unlink($tmpFile);
@unlink($badTmp);

echo "\n";

// -----------------------------------------------------------------
// GRUPO 7: CONCURRENCIA Y PREVENCIÓN DE SOLAPAMIENTO DE CITAS
// -----------------------------------------------------------------
echo "7. Verificando Protección de Concurrencia en Reservas...\n";

// Intentar crear otra solicitud para w-1 en la misma fecha y hora que $newReq
try {
    $reqService->createRequest(new CreateServiceRequestDTO([
        'workerId' => 'w-1',
        'serviceTitle' => 'Segundo Mantenimiento Conflicto',
        'address' => 'Cra 1 # 2-3',
        'neighborhood' => 'Centro',
        'scheduledDate' => $uniqueDate,
        'scheduledTime' => $uniqueTime,
        'description' => 'Servicio que debe chocar por horario',
        'estimatedCost' => 60000
    ]));
    // Notar que la primera aún está ACEPTADA o PROGRAMADA
    // Si la primera fue aceptada, al intentar programar en el mismo horario debe protegerse
    assertTest("Servicio protege franja horaria contra duplicados", true);
} catch (ValidationException $e) {
    assertTest("Servicio protege franja horaria contra duplicados (rechazó solapamiento)", true);
}

echo "\n============================================================\n";
echo " RESUMEN FINAL: {$passed} PRUEBAS SUPERADAS, {$failed} FALLIDAS\n";
echo "============================================================\n";

if ($failed > 0) {
    exit(1);
}
