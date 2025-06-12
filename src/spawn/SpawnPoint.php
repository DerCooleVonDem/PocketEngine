<?php

declare(strict_types=1);

namespace JonasWindmann\PocketEngine\spawn;

use JonasWindmann\CoreAPI\manager\Manageable;
use pocketmine\math\Vector3;
use pocketmine\world\World;

/**
 * Represents a spawn point with position, world, and metadata
 * Provides comprehensive spawn point management with validation
 */
class SpawnPoint implements Manageable
{
    private string $id;
    private Vector3 $position;
    private string $name;
    private bool $isAvailable;
    private ?string $worldName;
    private array $metadata;
    private int $priority;
    private string $type;

    public function __construct(
        string $id,
        Vector3 $position,
        string $name = "",
        bool $isAvailable = true,
        ?string $worldName = null,
        array $metadata = [],
        int $priority = 0,
        string $type = "default"
    ) {
        $this->id = $id;
        $this->position = $position;
        $this->name = $name ?: "Spawn Point $id";
        $this->isAvailable = $isAvailable;
        $this->worldName = $worldName;
        $this->metadata = $metadata;
        $this->priority = $priority;
        $this->type = $type;
    }

    /**
     * Get the spawn point ID
     * 
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the spawn point position
     * 
     * @return Vector3
     */
    public function getPosition(): Vector3
    {
        return $this->position;
    }

    /**
     * Set the spawn point position
     * 
     * @param Vector3 $position
     */
    public function setPosition(Vector3 $position): void
    {
        $this->position = $position;
    }

    /**
     * Get the spawn point name
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the spawn point name
     * 
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Check if the spawn point is available for use
     * 
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    /**
     * Set the availability status of the spawn point
     *
     * @param bool $isAvailable
     */
    public function setAvailable(bool $isAvailable): void
    {
        $this->isAvailable = $isAvailable;
    }

    /**
     * Get the world name for this spawn point
     *
     * @return string|null
     */
    public function getWorldName(): ?string
    {
        return $this->worldName;
    }

    /**
     * Set the world name for this spawn point
     *
     * @param string|null $worldName
     */
    public function setWorldName(?string $worldName): void
    {
        $this->worldName = $worldName;
    }

    /**
     * Get all metadata for this spawn point
     *
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Set metadata for this spawn point
     *
     * @param array $metadata
     */
    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    /**
     * Get a specific metadata value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getMetadataValue(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Set a specific metadata value
     *
     * @param string $key
     * @param mixed $value
     */
    public function setMetadataValue(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }

    /**
     * Get the priority of this spawn point
     *
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Set the priority of this spawn point
     *
     * @param int $priority
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * Get the type of this spawn point
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set the type of this spawn point
     *
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Convert spawn point to array for serialization
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'x' => $this->position->x,
            'y' => $this->position->y,
            'z' => $this->position->z,
            'world' => $this->worldName,
            'available' => $this->isAvailable,
            'metadata' => $this->metadata,
            'priority' => $this->priority,
            'type' => $this->type
        ];
    }

    /**
     * Create spawn point from array data
     *
     * @param array $data
     * @return SpawnPoint|null
     */
    public static function fromArray(array $data): ?SpawnPoint
    {
        if (!isset($data['id'], $data['x'], $data['y'], $data['z'])) {
            return null;
        }

        $position = new Vector3((float) $data['x'], (float) $data['y'], (float) $data['z']);

        return new SpawnPoint(
            $data['id'],
            $position,
            $data['name'] ?? "Spawn Point {$data['id']}",
            $data['available'] ?? true,
            $data['world'] ?? null,
            $data['metadata'] ?? [],
            $data['priority'] ?? 0,
            $data['type'] ?? 'default'
        );
    }

    /**
     * Get a string representation of the spawn point
     *
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            "SpawnPoint[id=%s, name=%s, pos=%.1f,%.1f,%.1f, world=%s, type=%s, priority=%d, available=%s]",
            $this->id,
            $this->name,
            $this->position->x,
            $this->position->y,
            $this->position->z,
            $this->worldName ?? 'null',
            $this->type,
            $this->priority,
            $this->isAvailable ? 'true' : 'false'
        );
    }
}
