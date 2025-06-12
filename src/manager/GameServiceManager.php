<?php

declare(strict_types=1);

namespace JonasWindmann\PocketEngine\manager;

use JonasWindmann\CoreAPI\manager\BaseManager;
use JonasWindmann\PocketEngine\game\GameService;
use pocketmine\plugin\Plugin;
use pocketmine\Server;

/**
 * Manages game services with proper lifecycle and event handling
 * 
 * @extends BaseManager
 */
class GameServiceManager extends BaseManager
{
    private bool $servicesStarted = false;

    public function __construct(Plugin $plugin)
    {
        parent::__construct($plugin);
    }

    /**
     * Register a game service
     * 
     * @param GameService $gameService
     * @return bool
     */
    public function registerGameService(GameService $gameService): bool
    {
        if ($this->addItem($gameService)) {
            // Register as event listener
            Server::getInstance()->getPluginManager()->registerEvents($gameService, $this->plugin);
            
            // If services are already started, start this one immediately
            if ($this->servicesStarted) {
                $gameService->start();
            }
            
            $this->plugin->getLogger()->info("Registered game service: " . $gameService->getId());
            return true;
        }
        
        $this->plugin->getLogger()->warning("Failed to register game service: " . $gameService->getId() . " (already exists)");
        return false;
    }

    /**
     * Unregister a game service
     * 
     * @param string $id
     * @return bool
     */
    public function unregisterGameService(string $id): bool
    {
        $service = $this->getGameService($id);
        if ($service) {
            // Stop the service if it's running
            if ($this->servicesStarted) {
                $service->stop();
            }
            
            if ($this->removeItem($id)) {
                $this->plugin->getLogger()->info("Unregistered game service: $id");
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get a game service by ID
     * 
     * @param string $id
     * @return GameService|null
     */
    public function getGameService(string $id): ?GameService
    {
        $item = $this->getItem($id);
        return $item instanceof GameService ? $item : null;
    }

    /**
     * Get all game services
     * 
     * @return GameService[]
     */
    public function getAllGameServices(): array
    {
        return array_filter($this->items, fn($item) => $item instanceof GameService);
    }

    /**
     * Start all registered game services
     */
    public function startAllServices(): void
    {
        if ($this->servicesStarted) {
            $this->plugin->getLogger()->warning("Game services are already started!");
            return;
        }

        foreach ($this->getAllGameServices() as $service) {
            try {
                $service->start();
                $this->plugin->getLogger()->debug("Started game service: " . $service->getId());
            } catch (\Exception $e) {
                $this->plugin->getLogger()->error("Failed to start game service " . $service->getId() . ": " . $e->getMessage());
            }
        }

        $this->servicesStarted = true;
        $this->plugin->getLogger()->info("Started " . count($this->getAllGameServices()) . " game services.");
    }

    /**
     * Stop all game services
     */
    public function stopAllServices(): void
    {
        if (!$this->servicesStarted) {
            return;
        }

        foreach ($this->getAllGameServices() as $service) {
            try {
                $service->stop();
                $this->plugin->getLogger()->debug("Stopped game service: " . $service->getId());
            } catch (\Exception $e) {
                $this->plugin->getLogger()->error("Failed to stop game service " . $service->getId() . ": " . $e->getMessage());
            }
        }

        $this->servicesStarted = false;
        $this->plugin->getLogger()->info("Stopped all game services.");
    }

    /**
     * Update all game services
     */
    public function updateAllServices(): void
    {
        if (!$this->servicesStarted) {
            return;
        }

        foreach ($this->getAllGameServices() as $service) {
            try {
                $service->update();
            } catch (\Exception $e) {
                $this->plugin->getLogger()->error("Error updating game service " . $service->getId() . ": " . $e->getMessage());
            }
        }
    }

    /**
     * Check if services are started
     * 
     * @return bool
     */
    public function areServicesStarted(): bool
    {
        return $this->servicesStarted;
    }

    /**
     * Get service count
     * 
     * @return int
     */
    public function getServiceCount(): int
    {
        return count($this->getAllGameServices());
    }

    /**
     * Check if a service exists
     * 
     * @param string $id
     * @return bool
     */
    public function hasService(string $id): bool
    {
        return $this->getGameService($id) !== null;
    }

    /**
     * Get services by type/category
     * 
     * @param string $type
     * @return GameService[]
     */
    public function getServicesByType(string $type): array
    {
        return array_filter($this->getAllGameServices(), function(GameService $service) use ($type) {
            return method_exists($service, 'getType') && $service->getType() === $type;
        });
    }
}
