<?php

declare(strict_types=1);

namespace JonasWindmann\PocketEngine\manager;

use JonasWindmann\CoreAPI\CoreAPI;
use JonasWindmann\CoreAPI\scoreboard\factory\ScoreboardFactory;
use JonasWindmann\CoreAPI\scoreboard\ScoreboardLine;
use JonasWindmann\PocketEngine\Main;
use pocketmine\plugin\Plugin;

/**
 * Manages scoreboard setup and configuration for PocketEngine
 */
class ScoreboardSetupManager
{
    private Plugin $plugin;
    private array $scoreboards = [];

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Setup all scoreboards for the game
     */
    public function setupScoreboards(): void
    {
        $configManager = Main::getInstance()->getConfigurationManager();
        $minigameName = $configManager->getMinigameName();
        $isSetupMode = $configManager->isSetupMode();

        $this->setupWaitingScoreboard($isSetupMode);
        $this->setupPlayingScoreboard($minigameName);
        $this->setupVictoryScoreboard();

        $this->plugin->getLogger()->info("Setup " . count($this->scoreboards) . " scoreboards.");
    }

    /**
     * Setup the waiting scoreboard
     * 
     * @param bool $isSetupMode
     */
    private function setupWaitingScoreboard(bool $isSetupMode): void
    {
        $scoreboard = ScoreboardFactory::createServerInfo(
            "waiting",
            "§6§l--- Waiting... ---",
            $this->plugin->getName(),
            !$isSetupMode // Auto-display enabled only when not in setup mode
        );

        $this->addScoreboardLines($scoreboard, [
            1 => "                     ",
            2 => "§7Players: §a{online}",
            3 => "                      ",
            4 => "§7Waiting...",
            5 => "                       "
        ]);

        CoreAPI::getInstance()->getScoreboardManager()->registerScoreboard($scoreboard);
        $this->scoreboards["waiting"] = $scoreboard;
    }

    /**
     * Setup the playing scoreboard
     * 
     * @param string $minigameName
     */
    private function setupPlayingScoreboard(string $minigameName): void
    {
        $scoreboard = ScoreboardFactory::createServerInfo(
            "playing",
            "§a§l--- $minigameName ---",
            $this->plugin->getName(),
            false // Auto-display disabled - manually controlled
        );

        $this->addScoreboardLines($scoreboard, [
            1 => " ",
            2 => "§7Players: §a{online}",
            3 => "  ",
            4 => "§7Time left: §a{time_left}",
            5 => "   ",
            6 => "{objective_1}",
            7 => "{objective_2}",
            8 => "    "
        ]);

        CoreAPI::getInstance()->getScoreboardManager()->registerScoreboard($scoreboard);
        $this->scoreboards["playing"] = $scoreboard;
    }

    /**
     * Setup the victory scoreboard
     */
    private function setupVictoryScoreboard(): void
    {
        $scoreboard = ScoreboardFactory::createServerInfo(
            "victory",
            "§e§l--- Round Ended ---",
            $this->plugin->getName(),
            false // Auto-display disabled - manually controlled
        );

        $this->addScoreboardLines($scoreboard, [
            1 => "                     ",
            2 => "§7Players: §a{online}",
            3 => "                      ",
            4 => "{round_finish_text}",
            5 => "{round_finish_reason}",
            6 => "{reset_countdown}",
            7 => "                       "
        ]);

        CoreAPI::getInstance()->getScoreboardManager()->registerScoreboard($scoreboard);
        $this->scoreboards["victory"] = $scoreboard;
    }

    /**
     * Add lines to a scoreboard
     * 
     * @param mixed $scoreboard
     * @param array $lines
     */
    private function addScoreboardLines($scoreboard, array $lines): void
    {
        foreach ($lines as $position => $text) {
            $scoreboard->addLine(new ScoreboardLine($text, $position));
        }
    }

    /**
     * Get a registered scoreboard by ID
     * 
     * @param string $id
     * @return mixed|null
     */
    public function getScoreboard(string $id)
    {
        return $this->scoreboards[$id] ?? null;
    }

    /**
     * Get all registered scoreboards
     * 
     * @return array
     */
    public function getAllScoreboards(): array
    {
        return $this->scoreboards;
    }

    /**
     * Update scoreboard configuration (useful for dynamic changes)
     * 
     * @param string $id
     * @param array $config
     */
    public function updateScoreboardConfig(string $id, array $config): void
    {
        $scoreboard = $this->getScoreboard($id);
        if (!$scoreboard) {
            $this->plugin->getLogger()->warning("Attempted to update non-existent scoreboard: $id");
            return;
        }

        // Update title if provided
        if (isset($config['title'])) {
            $scoreboard->setTitle($config['title']);
        }

        // Update lines if provided
        if (isset($config['lines']) && is_array($config['lines'])) {
            // Clear existing lines
            $scoreboard->clearLines();
            
            // Add new lines
            $this->addScoreboardLines($scoreboard, $config['lines']);
        }

        $this->plugin->getLogger()->info("Updated scoreboard configuration for: $id");
    }

    /**
     * Refresh all scoreboards with current configuration
     */
    public function refreshScoreboards(): void
    {
        $this->plugin->getLogger()->info("Refreshing all scoreboards...");
        
        // Clear existing scoreboards
        foreach ($this->scoreboards as $id => $scoreboard) {
            CoreAPI::getInstance()->getScoreboardManager()->unregisterScoreboard($id);
        }
        
        $this->scoreboards = [];
        
        // Re-setup all scoreboards
        $this->setupScoreboards();
    }

    /**
     * Check if a scoreboard exists
     * 
     * @param string $id
     * @return bool
     */
    public function hasScoreboard(string $id): bool
    {
        return isset($this->scoreboards[$id]);
    }

    /**
     * Get scoreboard count
     * 
     * @return int
     */
    public function getScoreboardCount(): int
    {
        return count($this->scoreboards);
    }
}
