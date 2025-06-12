<?php

declare(strict_types=1);

namespace JonasWindmann\PocketEngine;

use JonasWindmann\CoreAPI\CoreAPI;
use JonasWindmann\CoreAPI\session\SimpleComponentFactory;
use JonasWindmann\PocketEngine\command\PocketEngineCommand;
use JonasWindmann\PocketEngine\command\SpawnPointCommand;
use JonasWindmann\PocketEngine\components\PocketEngineComponent;
use JonasWindmann\PocketEngine\game\Game;
use JonasWindmann\PocketEngine\game\listener\GameListener;
use JonasWindmann\PocketEngine\manager\ConfigurationManager;
use JonasWindmann\PocketEngine\manager\GameServiceManager;
use JonasWindmann\PocketEngine\manager\ScoreboardSetupManager;
use JonasWindmann\PocketEngine\manager\SpawnPointManager;
use JonasWindmann\PocketEngine\manager\WorldManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Main extends PluginBase{

    use SingletonTrait;

    private ConfigurationManager $configurationManager;
    private WorldManager $worldManager;
    private SpawnPointManager $spawnPointManager;
    private GameServiceManager $gameServiceManager;
    private ScoreboardSetupManager $scoreboardSetupManager;

    public Game $game;

    protected function onEnable(): void
    {
        self::setInstance($this);

        // Initialize managers
        $this->configurationManager = new ConfigurationManager($this);
        $this->worldManager = new WorldManager($this);
        $this->spawnPointManager = new SpawnPointManager($this);
        $this->gameServiceManager = new GameServiceManager($this);
        $this->scoreboardSetupManager = new ScoreboardSetupManager($this);

        // Load configuration
        $this->configurationManager->loadConfiguration();

        // Log server mode
        if($this->configurationManager->isSetupMode()) {
            $this->getLogger()->info("§aThis server is in setup mode.");
        } else {
            $this->getLogger()->info("§cThis server is in production mode.");
        }

        // Register commands and session component
        CoreAPI::getInstance()->getCommandManager()->registerCommand(new PocketEngineCommand());
        CoreAPI::getInstance()->getCommandManager()->registerCommand(new SpawnPointCommand());
        CoreAPI::getInstance()->getSessionManager()->registerComponentFactory(
            SimpleComponentFactory::createFactory("pocketengine", function() {
                return new PocketEngineComponent();
            })
        );

        // Setup scoreboards
        $this->scoreboardSetupManager->setupScoreboards();

        // Handle world management
        $this->worldManager->handleWorldManagement();

        // Initialize game
        $requiredPlayers = $this->configurationManager->getRequiredPlayers();
        $roundTime = $this->configurationManager->getRoundTime();
        $this->game = new Game($requiredPlayers, $roundTime);

        // Register event listeners
        $this->getServer()->getPluginManager()->registerEvents(new GameListener(), $this);
    }

    protected function onDisable(): void
    {
        // Stop all game services
        $this->getGameServiceManager()->stopAllServices();
    }

    /**
     * Get the configuration manager
     *
     * @return ConfigurationManager
     */
    public function getConfigurationManager(): ConfigurationManager
    {
        return $this->configurationManager;
    }

    /**
     * Get the world manager
     *
     * @return WorldManager
     */
    public function getWorldManager(): WorldManager
    {
        return $this->worldManager;
    }

    /**
     * Get the spawn point manager
     *
     * @return SpawnPointManager
     */
    public function getSpawnPointManager(): SpawnPointManager
    {
        return $this->spawnPointManager;
    }

    /**
     * Get the game service manager
     *
     * @return GameServiceManager
     */
    public function getGameServiceManager(): GameServiceManager
    {
        return $this->gameServiceManager;
    }

    /**
     * Get the scoreboard setup manager
     *
     * @return ScoreboardSetupManager
     */
    public function getScoreboardSetupManager(): ScoreboardSetupManager
    {
        return $this->scoreboardSetupManager;
    }
}
