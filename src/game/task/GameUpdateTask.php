<?php

declare(strict_types=1);

namespace JonasWindmann\PocketEngine\game\task;

use JonasWindmann\PocketEngine\game\Game;
use JonasWindmann\PocketEngine\Main;
use pocketmine\scheduler\Task;

/**
 * Task that handles game updates every tick
 */
class GameUpdateTask extends Task
{
    public function onRun(): void
    {
        try {
            $game = Game::getInstance();
            if ($game) {
                $game->update();
            }
        } catch (\Exception $e) {
            Main::getInstance()->getLogger()->error("Error in GameUpdateTask: " . $e->getMessage());
        }
    }
}