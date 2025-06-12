<?php

declare(strict_types=1);

namespace JonasWindmann\PocketEngine\command\subcommand\spawn_point;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\PocketEngine\Main;
use pocketmine\command\CommandSender;

/**
 * Subcommand for clearing all spawn points
 */
class ClearSpawnPointsSubCommand extends SubCommand
{
    public function __construct()
    {
        parent::__construct(
            "clear",
            "Clear all spawn points (requires confirmation)",
            "/spawnpoint clear [confirm]",
            0,
            1,
            "pocketengine.spawnpoint.clear"
        );
    }

    public function execute(CommandSender $sender, array $args): void
    {
        $spawnPointManager = Main::getInstance()->getSpawnPointManager();
        $spawnPointCount = $spawnPointManager->getSpawnPointCount();

        if ($spawnPointCount === 0) {
            $sender->sendMessage("§cNo spawn points to clear!");
            return;
        }

        // Check for confirmation
        $confirm = $args[0] ?? null;
        if ($confirm !== "confirm") {
            $sender->sendMessage("§eAre you sure you want to clear all $spawnPointCount spawn points?");
            $sender->sendMessage("§7This action cannot be undone!");
            $sender->sendMessage("§7Use '/spawnpoint clear confirm' to proceed.");
            return;
        }

        // Clear all spawn points
        $spawnPointManager->clearAllSpawnPoints();
        $sender->sendMessage("§aCleared all spawn points!");
        $sender->sendMessage("§7Removed $spawnPointCount spawn points from the server.");
    }
}
