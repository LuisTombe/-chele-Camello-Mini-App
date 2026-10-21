<?php
namespace App\Infrastructure\Container;

use PDO;
use App\Infrastructure\Repository\UserRepository;
use App\Infrastructure\Repository\WorkerRepository;
use App\Infrastructure\Repository\ServiceRepository;
use App\Infrastructure\Repository\ServiceRequestRepository;
use App\Infrastructure\Repository\ReviewRepository;
use App\Infrastructure\Repository\ChatMessageRepository;
use App\Infrastructure\Repository\AuditLogRepository;
use App\Infrastructure\Repository\ReportRepository;
use App\Infrastructure\Repository\CategoryRepository;
use App\Infrastructure\Repository\NotificationRepository;
use App\Infrastructure\Adapter\Mail\MailAdapterInterface;
use App\Infrastructure\Adapter\Mail\LocalFileMailAdapter;
use App\Infrastructure\Adapter\Storage\FileStorageAdapterInterface;
use App\Infrastructure\Adapter\Storage\LocalStorageAdapter;
use App\Infrastructure\Adapter\Geolocation\GeolocationAdapterInterface;
use App\Infrastructure\Adapter\Geolocation\HaversineGeolocationAdapter;
use App\Domain\Observer\EventDispatcher;
use App\Domain\Observer\AuditLogSubscriber;
use App\Domain\Observer\ChatBotNotificationSubscriber;
use App\Domain\Observer\ReputationSubscriber;
use App\Domain\Observer\NotificationSubscriber;
use App\Domain\Observer\ModerationActionSubscriber;
use App\Domain\Strategy\Search\SearchSortContext;
use App\Domain\Strategy\Search\DistanceSortingStrategy;
use App\Domain\Strategy\Search\RatingSortingStrategy;
use App\Domain\Strategy\Search\PriceSortingStrategy;
use App\Domain\Strategy\Search\ExperienceSortingStrategy;
use App\Application\Service\RequestService;
use App\Application\Service\WorkerService;
use App\Application\Service\ReviewService;
use App\Application\Service\ChatService;
use App\Application\Service\ReportService;
use App\Application\Service\AuthService;
use App\Application\Service\SearchService;
use App\Application\Service\NotificationService;

class AppContainer {
    private PDO $pdo;
    private ?EventDispatcher $dispatcher = null;
    private ?SearchSortContext $sortContext = null;
    private ?MailAdapterInterface $mailer = null;
    private ?FileStorageAdapterInterface $storage = null;
    private ?GeolocationAdapterInterface $geoAdapter = null;

    // Repositorios
    private ?UserRepository $userRepo = null;
    private ?WorkerRepository $workerRepo = null;
    private ?ServiceRepository $serviceRepo = null;
    private ?ServiceRequestRepository $requestRepo = null;
    private ?ReviewRepository $reviewRepo = null;
    private ?ChatMessageRepository $chatRepo = null;
    private ?AuditLogRepository $auditRepo = null;
    private ?ReportRepository $reportRepo = null;
    private ?CategoryRepository $categoryRepo = null;
    private ?NotificationRepository $notificationRepo = null;

    // Servicios
    private ?RequestService $requestService = null;
    private ?WorkerService $workerService = null;
    private ?ReviewService $reviewService = null;
    private ?ChatService $chatService = null;
    private ?ReportService $reportService = null;
    private ?AuthService $authService = null;
    private ?SearchService $searchService = null;
    private ?NotificationService $notificationService = null;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getEventDispatcher(): EventDispatcher {
        if ($this->dispatcher === null) {
            $this->dispatcher = new EventDispatcher();
            // Registrar suscriptores locales de dominio
            $this->dispatcher->addSubscriber(new AuditLogSubscriber($this->getAuditLogRepository()));
            $this->dispatcher->addSubscriber(new ChatBotNotificationSubscriber($this->getChatMessageRepository()));
            $this->dispatcher->addSubscriber(new ReputationSubscriber($this->getWorkerRepository(), $this->getReviewRepository()));
            $this->dispatcher->addSubscriber(new NotificationSubscriber($this->getNotificationService()));
            $this->dispatcher->addSubscriber(new ModerationActionSubscriber($this->getWorkerService(), $this->getAuthService()));
        }
        return $this->dispatcher;
    }

    public function getSearchSortContext(): SearchSortContext {
        if ($this->sortContext === null) {
            $this->sortContext = new SearchSortContext([
                new DistanceSortingStrategy(),
                new RatingSortingStrategy(),
                new PriceSortingStrategy(),
                new ExperienceSortingStrategy(),
            ]);
        }
        return $this->sortContext;
    }

    public function getMailAdapter(): MailAdapterInterface {
        if ($this->mailer === null) {
            $this->mailer = new LocalFileMailAdapter();
        }
        return $this->mailer;
    }

    public function getStorageAdapter(): FileStorageAdapterInterface {
        if ($this->storage === null) {
            $this->storage = new LocalStorageAdapter();
        }
        return $this->storage;
    }

    public function getGeolocationAdapter(): GeolocationAdapterInterface {
        if ($this->geoAdapter === null) {
            $this->geoAdapter = new HaversineGeolocationAdapter();
        }
        return $this->geoAdapter;
    }

    // Repositorios
    public function getUserRepository(): UserRepository {
        return $this->userRepo ??= new UserRepository($this->pdo);
    }

    public function getWorkerRepository(): WorkerRepository {
        return $this->workerRepo ??= new WorkerRepository($this->pdo);
    }

    public function getServiceRepository(): ServiceRepository {
        return $this->serviceRepo ??= new ServiceRepository($this->pdo);
    }

    public function getServiceRequestRepository(): ServiceRequestRepository {
        return $this->requestRepo ??= new ServiceRequestRepository($this->pdo);
    }

    public function getReviewRepository(): ReviewRepository {
        return $this->reviewRepo ??= new ReviewRepository($this->pdo);
    }

    public function getChatMessageRepository(): ChatMessageRepository {
        return $this->chatRepo ??= new ChatMessageRepository($this->pdo);
    }

    public function getAuditLogRepository(): AuditLogRepository {
        return $this->auditRepo ??= new AuditLogRepository($this->pdo);
    }

    public function getReportRepository(): ReportRepository {
        return $this->reportRepo ??= new ReportRepository($this->pdo);
    }

    public function getCategoryRepository(): CategoryRepository {
        return $this->categoryRepo ??= new CategoryRepository($this->pdo);
    }

    public function getNotificationRepository(): NotificationRepository {
        return $this->notificationRepo ??= new NotificationRepository($this->pdo);
    }

    // Servicios de Aplicación
    public function getRequestService(): RequestService {
        return $this->requestService ??= new RequestService(
            $this->getServiceRequestRepository(),
            $this->getWorkerRepository(),
            $this->getEventDispatcher()
        );
    }

    public function getWorkerService(): WorkerService {
        return $this->workerService ??= new WorkerService(
            $this->getWorkerRepository(),
            $this->getServiceRepository(),
            $this->getReviewRepository(),
            $this->getSearchSortContext(),
            $this->getCategoryRepository(),
            $this->getStorageAdapter()
        );
    }

    public function getSearchService(): SearchService {
        return $this->searchService ??= new SearchService(
            $this->getWorkerRepository(),
            $this->getServiceRepository(),
            $this->getSearchSortContext(),
            $this->getGeolocationAdapter()
        );
    }

    public function getNotificationService(): NotificationService {
        return $this->notificationService ??= new NotificationService(
            $this->getNotificationRepository(),
            $this->getMailAdapter()
        );
    }

    public function getReviewService(): ReviewService {
        return $this->reviewService ??= new ReviewService(
            $this->getReviewRepository(),
            $this->getWorkerRepository(),
            $this->getServiceRequestRepository(),
            $this->getEventDispatcher()
        );
    }

    public function getChatService(): ChatService {
        return $this->chatService ??= new ChatService(
            $this->getChatMessageRepository(),
            $this->getServiceRequestRepository(),
            $this->getStorageAdapter()
        );
    }

    public function getReportService(): ReportService {
        return $this->reportService ??= new ReportService(
            $this->getReportRepository(),
            $this->getEventDispatcher()
        );
    }

    public function getAuthService(): AuthService {
        return $this->authService ??= new AuthService(
            $this->getUserRepository(),
            $this->getMailAdapter()
        );
    }
}
