<?php

declare(strict_types=1);

namespace JonasWindmann\PocketEngine\manager;

use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;

/**
 * Manages plugin configuration with validation and type safety
 */
class ConfigurationManager
{
    private Plugin $plugin;
    private Config $config;
    private bool $setupMode = false;

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Load and validate configuration
     */
    public function loadConfiguration(): void
    {
        $this->plugin->saveResource("config.yml");
        $this->config = new Config($this->plugin->getDataFolder() . "config.yml", Config::YAML);
        
        $this->validateConfiguration();
        $this->setupMode = $this->config->get("setup-mode", false);
    }

    /**
     * Validate configuration values
     */
    private function validateConfiguration(): void
    {
        $requiredKeys = [
            "enable-auto-world-management",
            "setup-mode",
            "minigame-name",
            "required-players",
            "round-time"
        ];

        foreach ($requiredKeys as $key) {
            if (!$this->config->exists($key)) {
                $this->plugin->getLogger()->warning("Missing configuration key: $key. Using default value.");
            }
        }

        // Validate numeric values
        $requiredPlayers = $this->config->get("required-players", 2);
        if (!is_numeric($requiredPlayers) || $requiredPlayers < 1) {
            $this->plugin->getLogger()->warning("Invalid required-players value. Using default: 2");
            $this->config->set("required-players", 2);
        }

        $roundTime = $this->config->get("round-time", 5);
        if (!is_numeric($roundTime) || $roundTime < 1) {
            $this->plugin->getLogger()->warning("Invalid round-time value. Using default: 5");
            $this->config->set("round-time", 5);
        }
    }

    /**
     * Check if server is in setup mode
     * 
     * @return bool
     */
    public function isSetupMode(): bool
    {
        return $this->setupMode;
    }

    /**
     * Check if auto world management is enabled
     * 
     * @return bool
     */
    public function isAutoWorldManagementEnabled(): bool
    {
        return $this->config->get("enable-auto-world-management", false);
    }

    /**
     * Get the minigame name
     * 
     * @return string
     */
    public function getMinigameName(): string
    {
        return $this->config->get("minigame-name", "Minigame");
    }

    /**
     * Get required players count
     * 
     * @return int
     */
    public function getRequiredPlayers(): int
    {
        return (int) $this->config->get("required-players", 2);
    }

    /**
     * Get round time in minutes
     * 
     * @return int
     */
    public function getRoundTime(): int
    {
        return (int) $this->config->get("round-time", 5);
    }

    /**
     * Get the raw config object
     * 
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Save configuration to file
     */
    public function saveConfiguration(): void
    {
        $this->config->save();
    }
}
