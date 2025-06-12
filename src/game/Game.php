<?php

namespace JonasWindmann\PocketEngine\game;

use JonasWindmann\CoreAPI\CoreAPI;
use JonasWindmann\CoreAPI\scoreboard\ScoreboardTag;
use JonasWindmann\CoreAPI\utils\WorldUtils;
use JonasWindmann\PocketEngine\components\PocketEngineComponent;
use JonasWindmann\PocketEngine\game\task\GameUpdateTask;
use JonasWindmann\PocketEngine\game\task\PostGameCountdownTask;
use JonasWindmann\PocketEngine\game\task\StartGameCountdownTask;
use JonasWindmann\PocketEngine\Main;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\Position;

class Game
{
    use SingletonTrait;

    public array $players = [];

    public GameState $gameState = GameState::WAITING;

    public int $playersJoined = 0;
    public int $requiredPlayers = 1;

    public int $gameStartTime = 0;
    public int $maxGameTimeMinutes = 1;
    public int $postGameWaitTime = 30;

    public TaskHandler $updateHandler;
    public ?TaskHandler $postGameHandler = null;

    public bool $roundWasWon = false;
    public ?Player $winner = null;

    public array $spawnLocations = [];
    public array $unclaimedSpawnLocations = [];



    public function __construct(int $requiredPlayers, int $maxGameTimeMinutes, int $postGameWaitTime = 30)
    {
        self::setInstance($this);
        $this->requiredPlayers = $requiredPlayers;
        $this->maxGameTimeMinutes = $maxGameTimeMinutes;
        $this->postGameWaitTime = $postGameWaitTime;
    }

    public function registerGameService(GameService $gameService): bool {
        return Main::getInstance()->getGameServiceManager()->registerGameService($gameService);
    }

    public function getGameServices(): array {
        return Main::getInstance()->getGameServiceManager()->getAllGameServices();
    }

    public function getGameService(string $id): ?GameService {
        return Main::getInstance()->getGameServiceManager()->getGameService($id);
    }

    public function join(Player $player)
    {
        if($this->isSetupMode()) return;

        if($this->gameState !== GameState::WAITING) {
            $player->kick("§cThe round has already started.");
            return;
        }

        $this->playersJoined++;

        $world = WorldUtils::getWorldByName("lobby");
        if($world) {
            $player->teleport($world->getSpawnLocation());
            $player->sendMessage("§aWaiting for other players to join...");
        }

        $this->checkForStart();

        $this->players[$player->getUniqueId()->toString()] = $player;
    }

    public function leave(Player $player)
    {
        if($this->isSetupMode()) return;

        if($this->playersJoined > 0) {
            $this->playersJoined--;
        }

        unset($this->players[$player->getUniqueId()->toString()]);

        // Check if all players have left and reset if necessary
        $this->checkForReset();
    }

    public function isSetupMode(): bool {
        return Main::getInstance()->getConfigurationManager()->isSetupMode();
    }

    private function checkForStart()
    {
        if($this->playersJoined >= $this->requiredPlayers) {
            Main::getInstance()->getScheduler()->scheduleRepeatingTask(new StartGameCountdownTask(), 20);
        }
    }

    /**
     * Check if all players have left and reset the game if necessary
     */
    private function checkForReset(): void
    {
        // Only reset if we're in ENDED state and no players are left
        // PLAYING state is handled in update() method by calling end() first
        if ($this->gameState === GameState::ENDED && $this->playersJoined <= 0) {
            Main::getInstance()->getLogger()->info("All players left during post-game. Resetting server to waiting state.");
            $this->reset();
        }
    }

    public function start()
    {
        $this->loadSpawnPoints();
        $this->unclaimedSpawnLocations = $this->spawnLocations;

        $world = WorldUtils::getWorldByName("game");
        foreach($this->players as $player) {
            if($world && $player instanceof Player) {
                $player->getInventory()->clearAll();
                $vec = $this->selectSpawnPoint($player);
                if ($vec) {
                    $player->teleport(new Position($vec->x + 0.5, $vec->y, $vec->z + 0.5, $world));
                    $player->sendMessage("§aThe round has started!");
                    CoreAPI::getInstance()->getScoreboardManager()->displayScoreboard($player, "playing");
                } else {
                    $player->sendMessage("§cNo spawn point available!");
                }
            }
        }

        $this->gameStartTime = time();
        $task = new GameUpdateTask();
        Main::getInstance()->getScheduler()->scheduleRepeatingTask($task, 1);
        $this->updateHandler = $task->getHandler();

        $this->gameState = GameState::PLAYING;

        // Start all game services
        Main::getInstance()->getGameServiceManager()->startAllServices();
    }

    // called every tick (20 ticks = 1 second)
    public function update()
    {
        // Check if all players have left during the game - end the round properly
        if ($this->gameState === GameState::PLAYING && $this->playersJoined <= 0) {
            Main::getInstance()->getLogger()->info("All players left during the game. Ending round.");
            $this->end();
            return;
        }

        $scoreboard = CoreAPI::getInstance()->getScoreboardManager()->getScoreboard("playing");
        if($scoreboard) {
            // Time left (Format: Minutes:Seconds
            $scoreboard->addTag(new ScoreboardTag("time_left", function() {
                $secondsLeft = $this->gameStartTime + ($this->maxGameTimeMinutes * 60) - time();
                $minutes = floor($secondsLeft / 60);
                $seconds = $secondsLeft % 60;
                return "$minutes:$seconds";
            }, "Time left in minutes"));
        }

        // check if time is up
        if(time() - $this->gameStartTime >= $this->maxGameTimeMinutes * 60) {
            $this->end();
            return;
        }

        // Update all game services
        Main::getInstance()->getGameServiceManager()->updateAllServices();
    }

    public function end()
    {
        $this->gameState = GameState::ENDED;
        $this->updateHandler->cancel();

        // Stop all game services
        Main::getInstance()->getGameServiceManager()->stopAllServices();

        $scoreboard = CoreAPI::getInstance()->getScoreboardManager()->getScoreboard("victory");
        if($scoreboard) {
            // Set round finish text based on how the round ended
            $scoreboard->addTag(new ScoreboardTag("round_finish_text", function() {
                return $this->roundWasWon && $this->winner ? "§7Winner" : "§7Round Over";
            }, "Round finish status"));

            // Set round finish reason based on how the round ended
            $scoreboard->addTag(new ScoreboardTag("round_finish_reason", function() {
                if ($this->roundWasWon && $this->winner) {
                    // Player won - show green player name
                    return "§a" . $this->winner->getName();
                } else {
                    // Time ended - show time up message
                    return "§cTime is up!";
                }
            }, "Round finish reason"));
        }

        // Only handle victory world and scoreboards if there are players left
        if ($this->playersJoined > 0) {
            $victoryWorld = WorldUtils::getWorldByName("victory");
            foreach($this->players as $player) {
                if($victoryWorld && $player instanceof Player) {
                    $player->getInventory()->clearAll();
                    $player->teleport($victoryWorld->getSpawnLocation());
                    $player->sendMessage("§aThe round has ended!");

                    if($this->roundWasWon && $this->winner && $player->getName() === $this->winner->getName()) {
                        $player->sendTitle("§aYou won!");
                    } else {
                        $player->sendTitle("§cYou lost!");
                    }

                    CoreAPI::getInstance()->getScoreboardManager()->displayScoreboard($player, "victory");
                }
            }
        }

        // Start post-game countdown (will immediately reset if no players)
        $this->startPostGameCountdown();
    }

    /**
     * Start the post-game countdown timer
     */
    private function startPostGameCountdown(): void
    {
        $task = new PostGameCountdownTask($this->postGameWaitTime);
        $this->postGameHandler = Main::getInstance()->getScheduler()->scheduleRepeatingTask($task, 20);

        Main::getInstance()->getLogger()->info("Post-game countdown started. Server will reset in {$this->postGameWaitTime} seconds.");
    }

    public function selectSpawnPoint(Player $player): ?Vector3 {
        $session = CoreAPI::getInstance()->getSessionManager()->getSessionByPlayer($player);
        if (!$session) {
            return null;
        }

        $pocketengineComponent = $session->getComponent("pocketengine");
        if (!$pocketengineComponent instanceof PocketEngineComponent) {
            return null;
        }

        if (empty($this->unclaimedSpawnLocations)) {
            Main::getInstance()->getLogger()->warning("No unclaimed spawn points available for player " . $player->getName());
            return null;
        }

        $randomSpawnPoint = $this->unclaimedSpawnLocations[array_rand($this->unclaimedSpawnLocations)];
        $pocketengineComponent->setSpawnPoint($randomSpawnPoint);

        // Remove spawn point from unclaimed list
        $key = array_search($randomSpawnPoint, $this->unclaimedSpawnLocations, true);
        if ($key !== false) {
            unset($this->unclaimedSpawnLocations[$key]);
        }

        return $randomSpawnPoint;
    }

    public function loadSpawnPoints(): void
    {
        $spawnPointManager = Main::getInstance()->getSpawnPointManager();
        $this->spawnLocations = $spawnPointManager->getSpawnPointPositions();

        if (empty($this->spawnLocations)) {
            Main::getInstance()->getLogger()->warning("No spawn points configured! Players may not spawn correctly.");
        } else {
            Main::getInstance()->getLogger()->info("Loaded " . count($this->spawnLocations) . " spawn points.");
        }
    }

    public function saveSpawnPoints(): void {
        // Delegate to spawn point manager
        Main::getInstance()->getSpawnPointManager()->saveItems();
    }

    public function getSpawnPoint(string $id): ?Vector3 {
        $spawnPoint = Main::getInstance()->getSpawnPointManager()->getSpawnPoint($id);
        return $spawnPoint ? $spawnPoint->getPosition() : null;
    }

    public function addSpawnPoint(Vector3 $spawnPoint, ?string $name = null): string {
        return Main::getInstance()->getSpawnPointManager()->addSpawnPoint($spawnPoint, $name);
    }

    public function removeSpawnPoint(string $id): bool {
        $result = Main::getInstance()->getSpawnPointManager()->removeSpawnPoint($id);
        if ($result) {
            // Update local cache
            unset($this->spawnLocations[$id]);
        }
        return $result;
    }

    public function getSpawnPoints(): array {
        return $this->spawnLocations;
    }

    public function getPlayers()
    {
        return $this->players;
    }

    public function playerHasWon(Player $player) {
        $this->roundWasWon = true;
        $this->winner = $player;
        $this->end();
    }

    public function getGameState()
    {
        return $this->gameState;
    }

    /**
     * Reset the game to initial waiting state
     */
    public function reset(): void
    {
        // Cancel any running tasks
        if (isset($this->updateHandler)) {
            $this->updateHandler->cancel();
        }
        if ($this->postGameHandler !== null) {
            $this->postGameHandler->cancel();
            $this->postGameHandler = null;
        }

        // Reset game properties
        $this->gameState = GameState::WAITING;
        $this->players = [];
        $this->playersJoined = 0;
        $this->gameStartTime = 0;
        $this->roundWasWon = false;
        $this->winner = null;
        $this->spawnLocations = [];
        $this->unclaimedSpawnLocations = [];

        Main::getInstance()->getLogger()->info("Game reset to waiting state.");
    }
}