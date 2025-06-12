<?php

declare(strict_types=1);

namespace JonasWindmann\PocketEngine\command\subcommand\spawn_point;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\PocketEngine\Main;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

/**
 * Subcommand for adding new spawn points
 */
class AddSpawnPointSubCommand extends SubCommand
{
    public function __construct()
    {
        parent::__construct(
            "add",
            "Add a new spawn point at your current location",
            "/spawnpoint add [name] [world] [type] [priority]",
            0,
            4,
            "pocketengine.spawnpoint.add"
        );
    }

    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cThis command can only be used by players.");
            return;
        }

        $position = $sender->getPosition()->floor();
        $spawnPointManager = Main::getInstance()->getSpawnPointManager();

        // Parse arguments
        $name = $args[0] ?? null;
        $worldName = $args[1] ?? $sender->getWorld()->getFolderName();
        $type = $args[2] ?? "default";
        $priority = isset($args[3]) ? (int) $args[3] : 0;

        // Validate priority
        if ($priority < 0) {
            $sender->sendMessage("§cPriority must be 0 or greater!");
            return;
        }

        // Add the spawn point
        $id = $spawnPointManager->addSpawnPoint($position, $name, $worldName, [], $priority, $type);
        
        $sender->sendMessage("§aSpawn point added successfully!");
        $sender->sendMessage("§7ID: §f$id");
        $sender->sendMessage("§7Name: §f" . ($name ?? "Spawn Point " . $spawnPointManager->getSpawnPointCount()));
        $sender->sendMessage("§7Position: §f" . $position->x . ", " . $position->y . ", " . $position->z);
        $sender->sendMessage("§7World: §f$worldName");
        $sender->sendMessage("§7Type: §f$type");
        $sender->sendMessage("§7Priority: §f$priority");
    }
}
