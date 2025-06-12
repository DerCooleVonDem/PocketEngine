<?php

declare(strict_types=1);

namespace JonasWindmann\PocketEngine\game;

use JonasWindmann\CoreAPI\manager\Manageable;
use pocketmine\event\Listener;

/**
 * Abstract base class for game services
 * Implements Manageable to work with CoreAPI's manager system
 */
abstract class GameService implements Listener, Manageable
{
    /**
     * Get the unique identifier for this service
     * This is used by the Manageable interface
     *
     * @return string
     */
    public abstract function getId(): string;

    /**
     * Start the service
     */
    public abstract function start(): void;

    /**
     * Update the service (called every tick during game)
     */
    public abstract function update(): void;

    /**
     * Stop the service
     */
    public abstract function stop(): void;

    /**
     * Get the current game instance
     *
     * @return Game
     */
    public function game(): Game {
        return Game::getInstance();
    }

    /**
     * Check if the game is currently in playing state
     *
     * @return bool
     */
    public function isPlaying(): bool {
        return $this->game()->gameState === GameState::PLAYING;
    }
}