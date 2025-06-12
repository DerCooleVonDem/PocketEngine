<?php

declare(strict_types=1);

namespace JonasWindmann\PocketEngine\manager;

use JonasWindmann\CoreAPI\utils\WorldUtils;
use JonasWindmann\PocketEngine\Main;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\world\World;

/**
 * Manages world creation, deletion, and configuration for PocketEngine
 */
class WorldManager
{
    private Plugin $plugin;
    private array $requiredWorlds = ["lobby", "game", "victory"];

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Handle complete world management process
     */
    public function handleWorldManagement(): void
    {
        $configManager = Main::getInstance()->getConfigurationManager();
        
        if (!$configManager->isAutoWorldManagementEnabled()) {
            $this->plugin->getLogger()->emergency("§cYou need to enable auto-world-management to use pocketengine. This will take control of your world folders and will delete everything! If you are sure enable it!");
            $this->plugin->getServer()->getPluginManager()->disablePlugin($this->plugin);
            return;
        }

        $this->cleanupUnwantedWorlds();
        $this->ensureRequiredWorldsExist();
        $this->configureWorldSettings();
    }

    /**
     * Remove all worlds except required ones
     */
    private function cleanupUnwantedWorlds(): void
    {
        $allWorlds = WorldUtils::getAllWorldFolders();
        
        foreach ($allWorlds as $world) {
            $worldName = basename($world);
            
            // Skip required worlds
            if (in_array($worldName, $this->requiredWorlds)) {
                continue;
            }

            // Handle default world specially
            if ($this->isDefaultWorld($worldName)) {
                $this->handleDefaultWorldChange($worldName);
                continue;
            }

            // Delete unwanted world
            if (!WorldUtils::deleteWorld($worldName)) {
                $this->plugin->getLogger()->error("§cFailed to delete world: " . $worldName);
            } else {
                $this->plugin->getLogger()->info("§aDeleted unwanted world: " . $worldName);
            }
        }
    }

    /**
     * Check if a world is the default world
     * 
     * @param string $worldName
     * @return bool
     */
    private function isDefaultWorld(string $worldName): bool
    {
        $defaultWorld = Server::getInstance()->getWorldManager()->getDefaultWorld();
        return $defaultWorld && $worldName === $defaultWorld->getFolderName();
    }

    /**
     * Handle changing the default world to lobby
     * 
     * @param string $currentDefaultWorld
     */
    private function handleDefaultWorldChange(string $currentDefaultWorld): void
    {
        $this->plugin->getLogger()->info("§cCould not delete default world $currentDefaultWorld, changing the default world to §elobby§c. Needs a restart!");
        
        if ($this->updateServerProperties()) {
            $this->plugin->getLogger()->info("§aChanged default world to §elobby§a. Please restart the server!");
            Server::getInstance()->shutdown();
        } else {
            $this->plugin->getLogger()->error("§cFailed to update server.properties file!");
        }
    }

    /**
     * Update server.properties to set lobby as default world
     * 
     * @return bool Success status
     */
    private function updateServerProperties(): bool
    {
        $serverPropertiesPath = $this->plugin->getServer()->getDataPath() . "server.properties";
        
        if (!file_exists($serverPropertiesPath)) {
            $this->plugin->getLogger()->error("§cserver.properties file not found!");
            return false;
        }

        $fileContents = file_get_contents($serverPropertiesPath);
        if ($fileContents === false) {
            $this->plugin->getLogger()->error("§cFailed to read server.properties file!");
            return false;
        }

        // Update world configuration
        $fileContents = preg_replace("/level-name=(.*)/m", "level-name=lobby", $fileContents);
        $fileContents = preg_replace("/level-type=(.*)/m", "level-type=FLAT", $fileContents);
        $fileContents = preg_replace("/level-seed=(.*)/m", "level-seed=", $fileContents);

        return file_put_contents($serverPropertiesPath, $fileContents) !== false;
    }

    /**
     * Ensure all required worlds exist
     */
    private function ensureRequiredWorldsExist(): void
    {
        foreach ($this->requiredWorlds as $worldName) {
            if (!WorldUtils::getWorldByName($worldName)) {
                $this->plugin->getLogger()->info("§aGenerating required world: " . $worldName);
                WorldUtils::generateWorld($worldName);
            }
        }
    }

    /**
     * Configure settings for all worlds (time, etc.)
     */
    private function configureWorldSettings(): void
    {
        foreach ($this->requiredWorlds as $worldName) {
            $world = WorldUtils::getWorldByName($worldName);
            if ($world) {
                $world->setTime(World::TIME_FULL);
                $world->stopTime();
                $this->plugin->getLogger()->debug("Configured world settings for: " . $worldName);
            }
        }
    }

    /**
     * Get list of required worlds
     * 
     * @return array
     */
    public function getRequiredWorlds(): array
    {
        return $this->requiredWorlds;
    }

    /**
     * Check if a world is required by PocketEngine
     * 
     * @param string $worldName
     * @return bool
     */
    public function isRequiredWorld(string $worldName): bool
    {
        return in_array($worldName, $this->requiredWorlds);
    }
}
