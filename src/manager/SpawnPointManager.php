<?php

declare(strict_types=1);

namespace JonasWindmann\PocketEngine\manager;

use JonasWindmann\CoreAPI\manager\BaseManager;
use JonasWindmann\PocketEngine\spawn\SpawnPoint;
use pocketmine\math\Vector3;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;

/**
 * Manages spawn points with persistence and validation
 * 
 * @extends BaseManager
 */
class SpawnPointManager extends BaseManager
{
    private Config $spawnPointsConfig;
    private string $configPath;

    public function __construct(Plugin $plugin)
    {
        parent::__construct($plugin);
        $this->configPath = $plugin->getDataFolder() . "spawnpoints.yml";
        $this->loadItems();
    }

    /**
     * Load spawn points from configuration file
     */
    public function loadItems(): void
    {
        if (!file_exists($this->configPath)) {
            $this->plugin->getLogger()->info("No spawn points configuration found. Creating new file.");
            $this->createDefaultConfig();
            return;
        }

        $this->spawnPointsConfig = new Config($this->configPath, Config::YAML);
        $this->items = [];

        foreach ($this->spawnPointsConfig->getAll() as $id => $data) {
            try {
                $spawnPoint = $this->parseSpawnPointData($id, $data);
                if ($spawnPoint) {
                    $this->items[$id] = $spawnPoint;
                }
            } catch (\Exception $e) {
                $this->plugin->getLogger()->warning("Failed to load spawn point '$id': " . $e->getMessage());
            }
        }

        $this->plugin->getLogger()->info("Loaded " . count($this->items) . " spawn points.");
    }

    /**
     * Parse spawn point data from config
     *
     * @param string $id
     * @param mixed $data
     * @return SpawnPoint|null
     */
    private function parseSpawnPointData(string $id, $data): ?SpawnPoint
    {
        if (is_string($data)) {
            // Legacy format: "x:y:z" or "x:y:z:world"
            $parts = explode(":", $data);
            if (count($parts) >= 3) {
                $vector = new Vector3((float) $parts[0], (float) $parts[1], (float) $parts[2]);
                $worldName = $parts[3] ?? null;
                return new SpawnPoint($id, $vector, "Spawn Point $id", true, $worldName);
            }
        } elseif (is_array($data)) {
            // New enhanced format with full metadata support
            $data['id'] = $id; // Ensure ID is set
            return SpawnPoint::fromArray($data);
        }

        $this->plugin->getLogger()->warning("Invalid spawn point data format for ID: $id");
        return null;
    }

    /**
     * Save spawn points to configuration file
     */
    public function saveItems(): void
    {
        if (!file_exists($this->plugin->getDataFolder())) {
            mkdir($this->plugin->getDataFolder(), 0755, true);
        }

        $this->spawnPointsConfig = new Config($this->configPath, Config::YAML);
        $data = [];

        foreach ($this->items as $spawnPoint) {
            if ($spawnPoint instanceof SpawnPoint) {
                $spawnPointData = $spawnPoint->toArray();
                unset($spawnPointData['id']); // Remove ID since it's used as the key
                $data[$spawnPoint->getId()] = $spawnPointData;
            }
        }

        $this->spawnPointsConfig->setAll($data);
        $this->spawnPointsConfig->save();
    }

    /**
     * Create default configuration file
     */
    private function createDefaultConfig(): void
    {
        if (!file_exists($this->plugin->getDataFolder())) {
            mkdir($this->plugin->getDataFolder(), 0755, true);
        }

        $this->spawnPointsConfig = new Config($this->configPath, Config::YAML);
        $this->spawnPointsConfig->setAll([]);
        $this->spawnPointsConfig->save();
    }

    /**
     * Add a new spawn point
     *
     * @param Vector3 $position
     * @param string|null $name
     * @param string|null $worldName
     * @param array $metadata
     * @param int $priority
     * @param string $type
     * @return string The generated ID
     */
    public function addSpawnPoint(
        Vector3 $position,
        ?string $name = null,
        ?string $worldName = null,
        array $metadata = [],
        int $priority = 0,
        string $type = "default"
    ): string {
        $id = uniqid("spawn_");
        $name = $name ?? "Spawn Point " . (count($this->items) + 1);

        $spawnPoint = new SpawnPoint($id, $position, $name, true, $worldName, $metadata, $priority, $type);
        $this->addItem($spawnPoint);
        $this->saveItems();

        $worldInfo = $worldName ? " in world '$worldName'" : "";
        $this->plugin->getLogger()->info("Added spawn point '$name' at " . $position->x . ", " . $position->y . ", " . $position->z . $worldInfo);
        return $id;
    }

    /**
     * Remove a spawn point by ID
     * 
     * @param string $id
     * @return bool
     */
    public function removeSpawnPoint(string $id): bool
    {
        if ($this->removeItem($id)) {
            $this->saveItems();
            $this->plugin->getLogger()->info("Removed spawn point with ID: $id");
            return true;
        }
        return false;
    }

    /**
     * Get a spawn point by ID
     * 
     * @param string $id
     * @return SpawnPoint|null
     */
    public function getSpawnPoint(string $id): ?SpawnPoint
    {
        $item = $this->getItem($id);
        return $item instanceof SpawnPoint ? $item : null;
    }

    /**
     * Get all spawn points
     * 
     * @return SpawnPoint[]
     */
    public function getAllSpawnPoints(): array
    {
        return array_filter($this->items, fn($item) => $item instanceof SpawnPoint);
    }

    /**
     * Get a random spawn point
     * 
     * @return SpawnPoint|null
     */
    public function getRandomSpawnPoint(): ?SpawnPoint
    {
        $spawnPoints = $this->getAllSpawnPoints();
        if (empty($spawnPoints)) {
            return null;
        }

        return $spawnPoints[array_rand($spawnPoints)];
    }

    /**
     * Get spawn points as Vector3 array (for backward compatibility)
     * 
     * @return Vector3[]
     */
    public function getSpawnPointPositions(): array
    {
        $positions = [];
        foreach ($this->getAllSpawnPoints() as $spawnPoint) {
            $positions[$spawnPoint->getId()] = $spawnPoint->getPosition();
        }
        return $positions;
    }

    /**
     * Check if any spawn points exist
     * 
     * @return bool
     */
    public function hasSpawnPoints(): bool
    {
        return !empty($this->getAllSpawnPoints());
    }

    /**
     * Get spawn point count
     *
     * @return int
     */
    public function getSpawnPointCount(): int
    {
        return count($this->getAllSpawnPoints());
    }

    /**
     * Get spawn points by world
     *
     * @param string $worldName
     * @return SpawnPoint[]
     */
    public function getSpawnPointsByWorld(string $worldName): array
    {
        return array_filter($this->getAllSpawnPoints(), fn(SpawnPoint $sp) => $sp->getWorldName() === $worldName);
    }

    /**
     * Get spawn points by type
     *
     * @param string $type
     * @return SpawnPoint[]
     */
    public function getSpawnPointsByType(string $type): array
    {
        return array_filter($this->getAllSpawnPoints(), fn(SpawnPoint $sp) => $sp->getType() === $type);
    }

    /**
     * Get available spawn points
     *
     * @return SpawnPoint[]
     */
    public function getAvailableSpawnPoints(): array
    {
        return array_filter($this->getAllSpawnPoints(), fn(SpawnPoint $sp) => $sp->isAvailable());
    }

    /**
     * Get spawn points sorted by priority (highest first)
     *
     * @return SpawnPoint[]
     */
    public function getSpawnPointsByPriority(): array
    {
        $spawnPoints = $this->getAllSpawnPoints();
        usort($spawnPoints, fn(SpawnPoint $a, SpawnPoint $b) => $b->getPriority() <=> $a->getPriority());
        return $spawnPoints;
    }

    /**
     * Update an existing spawn point
     *
     * @param string $id
     * @param Vector3|null $position
     * @param string|null $name
     * @param bool|null $available
     * @param string|null $worldName
     * @param array|null $metadata
     * @param int|null $priority
     * @param string|null $type
     * @return bool
     */
    public function updateSpawnPoint(
        string $id,
        ?Vector3 $position = null,
        ?string $name = null,
        ?bool $available = null,
        ?string $worldName = null,
        ?array $metadata = null,
        ?int $priority = null,
        ?string $type = null
    ): bool {
        $spawnPoint = $this->getSpawnPoint($id);
        if (!$spawnPoint) {
            return false;
        }

        if ($position !== null) $spawnPoint->setPosition($position);
        if ($name !== null) $spawnPoint->setName($name);
        if ($available !== null) $spawnPoint->setAvailable($available);
        if ($worldName !== null) $spawnPoint->setWorldName($worldName);
        if ($metadata !== null) $spawnPoint->setMetadata($metadata);
        if ($priority !== null) $spawnPoint->setPriority($priority);
        if ($type !== null) $spawnPoint->setType($type);

        $this->saveItems();
        $this->plugin->getLogger()->info("Updated spawn point '$id'");
        return true;
    }

    /**
     * Get the best spawn point for a player (highest priority, available)
     *
     * @param string|null $worldName Filter by world
     * @param string|null $type Filter by type
     * @return SpawnPoint|null
     */
    public function getBestSpawnPoint(?string $worldName = null, ?string $type = null): ?SpawnPoint
    {
        $spawnPoints = $this->getAvailableSpawnPoints();

        if ($worldName !== null) {
            $spawnPoints = array_filter($spawnPoints, fn(SpawnPoint $sp) => $sp->getWorldName() === $worldName);
        }

        if ($type !== null) {
            $spawnPoints = array_filter($spawnPoints, fn(SpawnPoint $sp) => $sp->getType() === $type);
        }

        if (empty($spawnPoints)) {
            return null;
        }

        // Sort by priority and return the highest
        usort($spawnPoints, fn(SpawnPoint $a, SpawnPoint $b) => $b->getPriority() <=> $a->getPriority());
        return $spawnPoints[0];
    }

    /**
     * Clear all spawn points
     */
    public function clearAllSpawnPoints(): void
    {
        $count = count($this->items);
        $this->items = [];
        $this->saveItems();
        $this->plugin->getLogger()->info("Cleared all spawn points ($count removed)");
    }

    /**
     * Import spawn points from array
     *
     * @param array $data
     * @param bool $overwrite
     * @return int Number of spawn points imported
     */
    public function importSpawnPoints(array $data, bool $overwrite = false): int
    {
        $imported = 0;

        foreach ($data as $id => $spawnPointData) {
            if (!is_array($spawnPointData)) {
                continue;
            }

            $spawnPointData['id'] = $id;
            $spawnPoint = SpawnPoint::fromArray($spawnPointData);

            if ($spawnPoint === null) {
                continue;
            }

            if (!$overwrite && $this->getSpawnPoint($id) !== null) {
                continue;
            }

            $this->items[$id] = $spawnPoint;
            $imported++;
        }

        if ($imported > 0) {
            $this->saveItems();
            $this->plugin->getLogger()->info("Imported $imported spawn points");
        }

        return $imported;
    }

    /**
     * Export all spawn points to array
     *
     * @return array
     */
    public function exportSpawnPoints(): array
    {
        $data = [];
        foreach ($this->getAllSpawnPoints() as $spawnPoint) {
            $spawnPointData = $spawnPoint->toArray();
            unset($spawnPointData['id']);
            $data[$spawnPoint->getId()] = $spawnPointData;
        }
        return $data;
    }
}
