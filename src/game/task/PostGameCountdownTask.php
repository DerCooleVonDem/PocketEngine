<?php

declare(strict_types=1);

namespace JonasWindmann\PocketEngine\game\task;

use JonasWindmann\CoreAPI\CoreAPI;
use JonasWindmann\CoreAPI\scoreboard\ScoreboardTag;
use JonasWindmann\CoreAPI\utils\WorldUtils;
use JonasWindmann\PocketEngine\game\Game;
use JonasWindmann\PocketEngine\game\GameState;
use JonasWindmann\PocketEngine\Main;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

/**
 * Task that handles the countdown after a game ends before resetting to waiting state
 */
class PostGameCountdownTask extends Task
{
    private int $secondsLeft;
    private array $warningTimes = [30, 20, 10, 5, 4, 3, 2, 1];

    public function __construct(int $waitTimeSeconds)
    {
        $this->secondsLeft = $waitTimeSeconds;
    }

    public function onRun(): void
    {
        try {
            $game = Game::getInstance();
            if (!$game || $game->getGameState() !== GameState::ENDED) {
                $this->getHandler()->cancel();
                return;
            }

            // Update scoreboard with countdown
            $this->updateScoreboard();

            // Check if we should show a warning
            if (in_array($this->secondsLeft, $this->warningTimes)) {
                $this->showWarning();
            }

            // Check if countdown is finished
            if ($this->secondsLeft <= 0) {
                $this->resetServer();
                $this->getHandler()->cancel();
                return;
            }

            $this->secondsLeft--;
        } catch (\Exception $e) {
            Main::getInstance()->getLogger()->error("Error in PostGameCountdownTask: " . $e->getMessage());
            $this->getHandler()->cancel();
        }
    }

    /**
     * Update the victory scoreboard with countdown information
     */
    private function updateScoreboard(): void
    {
        $scoreboard = CoreAPI::getInstance()->getScoreboardManager()->getScoreboard("victory");
        if ($scoreboard) {
            $scoreboard->addTag(new ScoreboardTag("reset_countdown", function() {
                return "Reset in: " . $this->secondsLeft . "s";
            }, "Time until server reset"));
        }
    }

    /**
     * Show warning titles to all players
     */
    private function showWarning(): void
    {
        $game = Game::getInstance();
        if (!$game) return;

        $message = $this->secondsLeft > 1 ? 
            "§cServer resets in §e{$this->secondsLeft} §cseconds!" : 
            "§cServer resets in §e{$this->secondsLeft} §csecond!";

        foreach ($game->getPlayers() as $player) {
            if ($player instanceof Player && $player->isOnline()) {
                $player->sendTitle("§c⚠ WARNING ⚠", $message, 10, 20, 10);
            }
        }
    }

    /**
     * Reset the server to initial state
     */
    private function resetServer(): void
    {
        $game = Game::getInstance();
        if (!$game) return;

        Main::getInstance()->getLogger()->info("Post-game wait time finished. Resetting server to waiting state.");

        // Kick all remaining players
        $this->kickAllPlayers();

        // Reset game state to waiting
        $game->reset();

        Main::getInstance()->getLogger()->info("Server reset complete. Ready for new players.");
    }

    /**
     * Kick all players currently in the game
     */
    private function kickAllPlayers(): void
    {
        $game = Game::getInstance();
        if (!$game) return;

        $players = $game->getPlayers();
        foreach ($players as $player) {
            if ($player instanceof Player && $player->isOnline()) {
                $player->kick("§aRound Over!\n§7The server is resetting for the next round.\n§eRejoin to play again!");
            }
        }

        // Clear the players array
        $game->players = [];
        $game->playersJoined = 0;
    }
}
