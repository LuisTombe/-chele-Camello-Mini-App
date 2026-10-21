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
use App\Domain\Entity\Category;
use App\Domain\Entity\Notification;
use App\Domain\State\User\ActiveUserState;
use App\Domain\State\User\SuspendedUserState;
use App\Domain\State\User\BlockedUserState;
use App\Domain\Exception\InvalidStateTransitionException;
use App\Domain\Exception\UnauthorizedActionException;
use App\Domain\Exception\ValidationException;
use App\Domain\Exception\EntityNotFoundException;
use App\Application\DTO\RegisterUserDTO;
use App\Application\DTO\LoginDTO;
use App\Application\DTO\PasswordRecoveryDTO;
use App\Application\DTO\ResetPasswordDTO;
use App\Application\DTO\UpdateUserStatusDTO;
use App\Application\DTO\AssignRoleDTO;
use App\Application\DTO\UpdateWorkerProfileDTO;
use App\Application\DTO\UpdateLocationDTO;
use App\Application\DTO\CreateCategoryDTO;
use App\Application\DTO\UpdateServiceDTO;
use App\Application\DTO\CreateServiceRequestDTO;
use App\Application\DTO\ChangeRequestStatusDTO;
use App\Application\DTO\CreateReviewDTO;
use App\Application\DTO\CreateServiceDTO;
use App\Application\DTO\CreateReportDTO;
use App\Application\DTO\ResolveReportDTO;
use App\Application\DTO\SearchWorkersDTO;
use App\Application\DTO\MarkNotificationReadDTO;
use App\Domain\Event\SolicitudReprogramadaEvent;
use App\Domain\Event\ModeracionResueltaEvent;

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
    assertTest("DTO bloquea auto-asignación de rol ADMINISTRADOR", false);
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

// 1.3 Validación de DTO de Solicitudes: Fechas pasadas
try {
    new CreateServiceRequestDTO([
        'workerId' => 'w-1',
        'serviceTitle' => 'Arreglo fuga',
        'address' => 'Calle 8 # 5-10',
        'neighborhood' => 'Centro',
        'scheduledDate' => '2020-01-01',
        'scheduledTime' => '10:00',
        'description' => 'Fuga urgente',
        'estimatedCost' => 50000
    ]);
    assertTest("DTO rechaza fechas pasadas en solicitudes", false);
} catch (ValidationException $e) {
    assertTest("DTO rechaza fechas pasadas en solicitudes", true);
}

// 1.4 Validación de Costos Estimados positivos
try {
    new CreateServiceRequestDTO([
        'workerId' => 'w-1',
        'serviceTitle' => 'Mantenimiento',
        'address' => 'Cra 5 # 12-30',
        'neighborhood' => 'Quirinal',
        'scheduledDate' => date('Y-m-d', strtotime('+2 days')),
        'scheduledTime' => '14:00',
        'description' => 'Revisión preventiva',
        'estimatedCost' => -20000
    ]);
    assertTest("DTO rechaza costos estimados negativos o cero", false);
} catch (ValidationException $e) {
    assertTest("DTO rechaza costos estimados negativos o cero", true);
}

// 1.5 DTO Seguro de Usuario
$user = new User('usr-99', 'Carlos Cliente', 'carlos@test.com', '$2y$10$fakehashpassword', 'CLIENTE');
$safeArray = $user->toSafeArray();
assertTest("DTO de usuario no expone hashes de contraseña", !isset($safeArray['password_hash']) && !isset($safeArray['password']));

// 1.6 DTO de Recuperación de Contraseña
try {
    new PasswordRecoveryDTO(['email' => 'correo-invalido']);
    assertTest("DTO de recuperación rechaza correos inválidos", false);
} catch (ValidationException $e) {
    assertTest("DTO de recuperación rechaza correos inválidos", true);
}

$recoveryDto = new PasswordRecoveryDTO(['email' => 'usuario@sena.edu.co']);
assertTest("DTO de recuperación acepta email válido", $recoveryDto->email === 'usuario@sena.edu.co');

// 1.7 DTO de Reseteo de Contraseña
try {
    new ResetPasswordDTO(['token' => '', 'password' => '123456']);
    assertTest("DTO de reseteo exige token no vacío", false);
} catch (ValidationException $e) {
    assertTest("DTO de reseteo exige token no vacío", true);
}

echo "\n";

// -----------------------------------------------------------------
// GRUPO 2: PATRÓN STATE EN CICLO DE VIDA DE SOLICITUDES
// -----------------------------------------------------------------
echo "2. Verificando Patrón State en Ciclo de Vida de Solicitudes...\n";

$req = new ServiceRequest(
    'test-req-1', 'CAM-TEST-1', 'cli-1', 'Cliente Uno', 'w-1', 'Carlos Fontanero',
    'Arreglo Tubería', date('Y-m-d', strtotime('+2 days')), '10:00',
    'Cra 5 # 10-20', 'Cándido Leguízamo', 'Fuga de agua', 45000
);

assertTest("Estado inicial de la solicitud es PENDIENTE", $req->getStatus() === 'PENDIENTE');

// 2.1 Transición inválida PENDIENTE -> FINALIZADA
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

// 2.5 Reprogramación en estado PROGRAMADA
$newDate = date('Y-m-d', strtotime('+4 days'));
$req->schedule('TRABAJADOR', 'w-1', $newDate, '16:00');
assertTest("Reprogramación válida en estado PROGRAMADA", $req->getScheduledDate() === $newDate && $req->getScheduledTime() === '16:00');

// 2.6 Inicio de trabajo
$req->start('TRABAJADOR', 'w-1');
assertTest("Transición válida PROGRAMADA -> EN_PROCESO", $req->getStatus() === 'EN_PROCESO');

// 2.7 Finalización de trabajo
$req->finish('TRABAJADOR', 'w-1');
assertTest("Transición válida EN_PROCESO -> FINALIZADA", $req->getStatus() === 'FINALIZADA');

// 2.8 Estado terminal (no admite cancelaciones posteriores)
try {
    $req->cancel('CLIENTE', 'cli-1', 'Ya no quiero el servicio');
    assertTest("Estado terminal FINALIZADA rechaza cancelaciones posteriores", false);
} catch (InvalidStateTransitionException $e) {
    assertTest("Estado terminal FINALIZADA rechaza cancelaciones posteriores", true);
}

// 2.9 Solicitud rechazada no puede ser programada
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
// GRUPO 3: PATRÓN STATE EN PUBLICACIONES, REPORTES Y USUARIOS
// -----------------------------------------------------------------
echo "3. Verificando Patrón State en Publicaciones, Reportes y Cuentas...\n";

// 3.1 State en Servicios
$service = new Service('svc-test', 'w-1', 'Instalación de Grifos', 35000);
assertTest("Servicio recién creado inicia en ACTIVO", $service->getStatus() === 'ACTIVO');

$service->deactivate('TRABAJADOR');
assertTest("Trabajador desactiva servicio (ACTIVO -> INACTIVO)", $service->getStatus() === 'INACTIVO');

$service->activate('TRABAJADOR');
assertTest("Trabajador reactiva servicio (INACTIVO -> ACTIVO)", $service->getStatus() === 'ACTIVO');

$service->suspend('ADMINISTRADOR', 'Incumplimiento de términos');
assertTest("Administrador suspende servicio (ACTIVO -> SUSPENDIDO)", $service->getStatus() === 'SUSPENDIDO');

try {
    $service->activate('TRABAJADOR');
    assertTest("Trabajador no puede eludir suspensión administrativa", false);
} catch (UnauthorizedActionException $e) {
    assertTest("Trabajador no puede eludir suspensión administrativa", true);
}

$service->unsuspend('ADMINISTRADOR');
assertTest("Administrador levanta suspensión exitosamente", $service->getStatus() === 'INACTIVO');

// 3.2 State en Reportes de Moderación
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

// 3.3 State en Cuentas de Usuario (ms-usuarios)
$testUser = new User('usr-test-state', 'Juan Pérez', 'juan@test.com', password_hash('secreto123', PASSWORD_BCRYPT), 'CLIENTE');
assertTest("Cuenta de usuario recién creada está en estado ACTIVO", $testUser->getStatus() === 'ACTIVO' && $testUser->getState()->canLogin());

$testUser->suspend('ADMINISTRADOR', 'Comportamiento inadecuado');
assertTest("Administrador suspende cuenta de usuario (ACTIVO -> SUSPENDIDO)", $testUser->getStatus() === 'SUSPENDIDO' && !$testUser->getState()->canLogin());

$testUser->block('ADMINISTRADOR', 'Violación grave de políticas');
assertTest("Administrador bloquea cuenta de usuario (SUSPENDIDO -> BLOQUEADO)", $testUser->getStatus() === 'BLOQUEADO' && !$testUser->getState()->canLogin());

$testUser->activate('ADMINISTRADOR');
assertTest("Administrador reactiva cuenta (BLOQUEADO -> ACTIVO)", $testUser->getStatus() === 'ACTIVO' && $testUser->getState()->canLogin());

echo "\n";

// -----------------------------------------------------------------
// GRUPO 4: PATRÓN STRATEGY Y BÚSQUEDA
// -----------------------------------------------------------------
echo "4. Verificando Patrón Strategy en Búsqueda y Ordenamiento...\n";

$sortContext = $container->getSearchSortContext();

$mockWorkers = [
    ['id' => 'w-1', 'name' => 'Carlos', 'distance_km' => 3.5, 'rating' => 4.5, 'min_rate' => 50000, 'experience_years' => 4, 'total_reviews' => 10],
    ['id' => 'w-2', 'name' => 'María', 'distance_km' => 1.2, 'rating' => 4.9, 'min_rate' => 70000, 'experience_years' => 8, 'total_reviews' => 25],
    ['id' => 'w-3', 'name' => 'Jorge', 'distance_km' => 5.0, 'rating' => 4.9, 'min_rate' => 40000, 'experience_years' => 12, 'total_reviews' => 15],
];

// 4.1 Estrategia por Cercanía
$workersCopy = $mockWorkers;
$sortContext->apply($workersCopy, 'cercania');
assertTest("Strategy Cercanía: primer resultado es el más cercano (1.2 km)", $workersCopy[0]['id'] === 'w-2' && $workersCopy[2]['id'] === 'w-3');

// 4.2 Estrategia por Calificación
$workersCopy = $mockWorkers;
$sortContext->apply($workersCopy, 'calificacion');
assertTest("Strategy Calificación: primer resultado es la mejor calificada (4.9 y 25 reseñas)", $workersCopy[0]['id'] === 'w-2' && $workersCopy[1]['id'] === 'w-3');

// 4.3 Estrategia por Precio
$workersCopy = $mockWorkers;
$sortContext->apply($workersCopy, 'precio');
assertTest("Strategy Precio: primer resultado es el más económico ($40.000)", $workersCopy[0]['id'] === 'w-3' && $workersCopy[2]['id'] === 'w-2');

// 4.4 Estrategia por Experiencia
$workersCopy = $mockWorkers;
$sortContext->apply($workersCopy, 'experiencia');
assertTest("Strategy Experiencia: primer resultado es el más experimentado (12 años)", $workersCopy[0]['id'] === 'w-3');

// 4.5 Criterio desconocido
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
$notifRepo = $container->getNotificationRepository();
$dispatcher = $container->getEventDispatcher();

// 5.1 Creación de solicitud dispara AuditLog, ChatBot y Notificación
$reqService = $container->getRequestService();
$uniqueTime = date('H:i', strtotime('+1 hour'));
$uniqueDate = date('Y-m-d', strtotime('+6 days'));

$newReq = $reqService->createRequest(new CreateServiceRequestDTO([
    'workerId' => 'w-1',
    'serviceTitle' => 'Mantenimiento Test Observer',
    'address' => 'Calle 10 # 5-20',
    'neighborhood' => 'Centro',
    'scheduledDate' => $uniqueDate,
    'scheduledTime' => $uniqueTime,
    'description' => 'Servicio de prueba para Observer y Notificaciones',
    'estimatedCost' => 55000
]));

$chatMessages = $chatRepo->findByRequestId($newReq->getId());
assertTest("Observer ChatBotNotificationSubscriber creó mensaje inicial en chat", count($chatMessages) >= 1);

$workerNotifs = $notifRepo->findByUserId('w-1');
assertTest("Observer NotificationSubscriber creó notificación en bandeja para el trabajador", count($workerNotifs) >= 1);

// 5.2 Aceptación de solicitud genera notificación al cliente
$reqService->changeStatus(new ChangeRequestStatusDTO([
    'requestId' => $newReq->getId(),
    'status' => 'ACEPTADA',
    'role' => 'TRABAJADOR'
]));

$clientNotifs = $notifRepo->findByUserId('cli-1');
assertTest("Observer NotificationSubscriber notificó al cliente sobre aceptación", count($clientNotifs) >= 1);

// 5.3 Moderación resuelta sancionatoria activa ModerationActionSubscriber
$workerService = $container->getWorkerService();
$svcTemp = $workerService->addService(new CreateServiceDTO([
    'workerId' => 'w-1',
    'title' => 'Servicio Test Moderación',
    'estimatedPrice' => 40000,
    'priceType' => 'desde'
]));

$dispatcher->dispatch(new ModeracionResueltaEvent([
    'status' => 'RESUELTO',
    'target_type' => 'SERVICE',
    'target_id' => $svcTemp->getId(),
    'resolution_notes' => 'Se decide suspender la publicación por reclamo de usuario'
]));

$svcCheck = $container->getServiceRepository()->findById($svcTemp->getId());
assertTest("Observer ModerationActionSubscriber suspendió el servicio tras resolución", $svcCheck->getStatus() === 'SUSPENDIDO');

echo "\n";

// -----------------------------------------------------------------
// GRUPO 6: ADAPTERS (MAIL, ALMACENAMIENTO Y GEOLOCALIZACIÓN)
// -----------------------------------------------------------------
echo "6. Verificando Patrón Adapter (Mail, Archivos y Geolocalización)...\n";

// 6.1 Mail Adapter
$mailer = $container->getMailAdapter();
$sent = $mailer->send('cliente@sena.edu.co', 'Prueba de Adapter', '<p>Contenido de prueba</p>', true);
assertTest("Adapter Mail envía y registra correo exitosamente", $sent === true);

// 6.2 Storage Adapter
$storage = $container->getStorageAdapter();
$tmpFile = tempnam(sys_get_temp_dir(), 'test_img');
file_put_contents($tmpFile, "\xFF\xD8\xFF\xE0" . "fake image data");
$fileData = [
    'name' => 'foto_diagnostico.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => $tmpFile,
    'size' => 1024,
    'error' => 0
];

$savedUrl = $storage->save($fileData, 'diagnosticos');
assertTest("Adapter Storage guarda archivo con ruta relativa segura", str_starts_with($savedUrl, 'assets/uploads/diagnosticos/'));

// Bloqueo de ejecutable PHP
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

// 6.3 Geolocation Adapter (Haversine)
$geoAdapter = $container->getGeolocationAdapter();
$distCentroIpanema = $geoAdapter->calculateDistance(2.9273, -75.2819, 2.9340, -75.2680);
assertTest("Adapter Haversine calcula distancia geodésica real (aprox 1.7 km)", $distCentroIpanema >= 1.4 && $distCentroIpanema <= 2.0);

$coordsCandido = $geoAdapter->getNeighborhoodCoordinates('Cándido Leguízamo');
assertTest("Adapter Haversine resuelve coordenadas para barrios de Neiva", $coordsCandido !== null && abs($coordsCandido[0] - 2.9460) < 0.001);

echo "\n";

// -----------------------------------------------------------------
// GRUPO 7: REPOSITORIOS Y DTOs COMPLEMENTARIOS
// -----------------------------------------------------------------
echo "7. Verificando Repositorios de Categorías y Notificaciones...\n";

// 7.1 CategoryRepository
$catRepo = $container->getCategoryRepository();
$allCats = $catRepo->findAll();
assertTest("CategoryRepository retorna lista de categorías", count($allCats) > 0);

$newCatId = 'test-cat-' . time();
$catRepo->save(new Category($newCatId, 'Carpintería de Prueba', 'hammer', 'Servicios de madera'));
$catLoaded = $catRepo->findById($newCatId);
assertTest("CategoryRepository persiste y recupera categoría", $catLoaded !== null && $catLoaded->getName() === 'Carpintería de Prueba');

// 7.2 NotificationRepository y NotificationService
$notifService = $container->getNotificationService();
$testNotif = $notifService->createNotification('usr-notif-test', 'Bienvenida', 'Gracias por usar Échele Camello');
assertTest("NotificationService crea notificación correctamente", $testNotif->getId() !== '');

$userNotifs = $notifService->getUserNotifications('usr-notif-test');
assertTest("NotificationService lista notificaciones y cuenta no leídas", $userNotifs['unreadCount'] >= 1);

$notifService->markAsRead(new MarkNotificationReadDTO([
    'userId' => 'usr-notif-test',
    'markAll' => true
]));
$userNotifsAfter = $notifService->getUserNotifications('usr-notif-test', true);
assertTest("NotificationService marca notificaciones como leídas", count($userNotifsAfter['notifications']) === 0);

echo "\n";

// -----------------------------------------------------------------
// GRUPO 8: CONCURRENCIA Y PREVENCIÓN DE SOLAPAMIENTO
// -----------------------------------------------------------------
echo "8. Verificando Protección de Concurrencia en Reservas...\n";

// 8.1 Programar la solicitud para reservar efectivamente la franja en la agenda
$reqService->changeStatus(new ChangeRequestStatusDTO([
    'requestId' => $newReq->getId(),
    'status' => 'PROGRAMADA',
    'role' => 'TRABAJADOR',
    'scheduledDate' => $uniqueDate,
    'scheduledTime' => $uniqueTime
]));

// 8.2 Intentar agendar otra solicitud para w-1 en la misma fecha y hora
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
    assertTest("Servicio protege franja horaria contra duplicados", false);
} catch (ValidationException $e) {
    assertTest("Servicio protege franja horaria contra duplicados (rechazó solapamiento)", true);
}

echo "\n";

// -----------------------------------------------------------------
// GRUPO 9: API GATEWAY E INTEGRACIÓN GLOBAL
// -----------------------------------------------------------------
echo "9. Verificando API Gateway y Componentes Globales...\n";

$searchService = $container->getSearchService();
$searchResults = $searchService->search(new SearchWorkersDTO([
    'categoryId' => 'plomeria',
    'sortBy' => 'calificacion'
]));
assertTest("SearchService entrega resultados orquestados con DTO y Strategy", is_array($searchResults) && count($searchResults) > 0);

assertTest("AppContainer inyecta dependencias únicas sin Singleton manual", $container->getRequestService() === $container->getRequestService());

echo "\n============================================================\n";
echo " RESUMEN FINAL: {$passed} PRUEBAS SUPERADAS, {$failed} FALLIDAS\n";
echo "============================================================\n";

if ($failed > 0) {
    exit(1);
}
