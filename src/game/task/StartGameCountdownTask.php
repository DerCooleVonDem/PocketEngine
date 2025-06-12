<?php

declare(strict_types=1);

namespace JonasWindmann\PocketEngine\game\task;

use JonasWindmann\PocketEngine\game\Game;
use JonasWindmann\PocketEngine\Main;
use pocketmine\scheduler\Task;
use pocketmine\Server;

/**
 * Task that handles the countdown before a game starts
 */
class StartGameCountdownTask extends Task
{
    private int $seconds = 5;

    public function __construct(int $countdownSeconds = 5)
    {
        $this->seconds = $countdownSeconds;
    }

    public function onRun(): void
    {
        try {
            if ($this->seconds <= 0) {
                $this->getHandler()->cancel();

                $game = Game::getInstance();
                if ($game) {
                    $game->start();
                }
                return;
            }

            // Send countdown message to all players
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->sendTip("§eGame starts in §a" . $this->seconds . " §eseconds!");
            }

            $this->seconds--;
        } catch (\Exception $e) {
            Main::getInstance()->getLogger()->error("Error in StartGameCountdownTask: " . $e->getMessage());
            $this->getHandler()->cancel();
        }
    }

    /**
     * Get remaining seconds
     *
     * @return int
     */
    public function getRemainingSeconds(): int
    {
        return $this->seconds;
    }
}