<?php

declare(strict_types=1);

namespace JonasWindmann\PocketEngine\command\subcommand\spawn_point;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\PocketEngine\Main;
use pocketmine\command\CommandSender;

/**
 * Subcommand for showing detailed spawn point information
 */
class InfoSpawnPointSubCommand extends SubCommand
{
    public function __construct()
    {
        parent::__construct(
            "info",
            "Show detailed information about a spawn point",
            "/spawnpoint info <id>",
            1,
            1,
            "pocketengine.spawnpoint.info"
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

        // Display detailed information
        $sender->sendMessage("§6=== Spawn Point Information ===");
        $sender->sendMessage("§7ID: §f{$spawnPoint->getId()}");
        $sender->sendMessage("§7Name: §f{$spawnPoint->getName()}");
        $sender->sendMessage("§7Position: §f" . 
            round($spawnPoint->getPosition()->x, 2) . ", " . 
            round($spawnPoint->getPosition()->y, 2) . ", " . 
            round($spawnPoint->getPosition()->z, 2));
        $sender->sendMessage("§7World: §f" . ($spawnPoint->getWorldName() ?? "§7Unknown"));
        $sender->sendMessage("§7Type: §f{$spawnPoint->getType()}");
        $sender->sendMessage("§7Priority: §f{$spawnPoint->getPriority()}");
        $sender->sendMessage("§7Available: " . ($spawnPoint->isAvailable() ? "§aYes" : "§cNo"));

        // Show metadata if any
        $metadata = $spawnPoint->getMetadata();
        if (!empty($metadata)) {
            $sender->sendMessage("§7Metadata:");
            foreach ($metadata as $key => $value) {
                $sender->sendMessage("  §7- §e$key§7: §f$value");
            }
        } else {
            $sender->sendMessage("§7Metadata: §8None");
        }
    }
}
