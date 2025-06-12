<?php

declare(strict_types=1);

namespace JonasWindmann\PocketEngine\command\subcommand\spawn_point;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\PocketEngine\Main;
use pocketmine\command\CommandSender;

/**
 * Subcommand for removing spawn points
 */
class RemoveSpawnPointSubCommand extends SubCommand
{
    public function __construct()
    {
        parent::__construct(
            "remove",
            "Remove a spawn point by ID",
            "/spawnpoint remove <id>",
            1,
            1,
            "pocketengine.spawnpoint.remove"
        );
    }

    public function execute(CommandSender $sender, array $args): void
    {
        $id = $args[0];
        $spawnPointManager = Main::getInstance()->getSpawnPointManager();

        // Check if spawn point exists
        $spawnPoint = $spawnPointManager->getSpawnPoint($id);
        if ($spawnPoint === null) {
            $sender->sendMessage("§cSpawn point with ID '$id' not found!");
            $sender->sendMessage("§7Use '/spawnpoint list' to see all spawn points.");
            return;
        }

        // Remove the spawn point
        if ($spawnPointManager->removeSpawnPoint($id)) {
            $sender->sendMessage("§aSpawn point removed successfully!");
            $sender->sendMessage("§7Removed: §f{$spawnPoint->getName()} §7(ID: $id)");
            $sender->sendMessage("§7Position: §f" . 
                round($spawnPoint->getPosition()->x, 1) . ", " . 
                round($spawnPoint->getPosition()->y, 1) . ", " . 
                round($spawnPoint->getPosition()->z, 1));
        } else {
            $sender->sendMessage("§cFailed to remove spawn point!");
        }
    }
}
