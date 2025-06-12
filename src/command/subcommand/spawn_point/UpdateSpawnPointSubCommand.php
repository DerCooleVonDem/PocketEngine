<?php

declare(strict_types=1);

namespace JonasWindmann\PocketEngine\command\subcommand\spawn_point;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\PocketEngine\Main;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

/**
 * Subcommand for updating spawn point properties
 */
class UpdateSpawnPointSubCommand extends SubCommand
{
    public function __construct()
    {
        parent::__construct(
            "update",
            "Update spawn point properties",
            "/spawnpoint update <id> <property> <value>",
            3,
            3,
            "pocketengine.spawnpoint.update"
        );
    }

    public function execute(CommandSender $sender, array $args): void
    {
        $id = $args[0];
        $property = strtolower($args[1]);
        $value = $args[2];

        $spawnPointManager = Main::getInstance()->getSpawnPointManager();

        // Check if spawn point exists
        $spawnPoint = $spawnPointManager->getSpawnPoint($id);
        if ($spawnPoint === null) {
            $sender->sendMessage("§cSpawn point with ID '$id' not found!");
            return;
        }

        // Update based on property
        switch ($property) {
            case "name":
                $success = $spawnPointManager->updateSpawnPoint($id, name: $value);
                break;

            case "world":
                $success = $spawnPointManager->updateSpawnPoint($id, worldName: $value);
                break;

            case "type":
                $success = $spawnPointManager->updateSpawnPoint($id, type: $value);
                break;

            case "priority":
                $priority = (int) $value;
                if ($priority < 0) {
                    $sender->sendMessage("§cPriority must be 0 or greater!");
                    return;
                }
                $success = $spawnPointManager->updateSpawnPoint($id, priority: $priority);
                break;

            case "available":
                $available = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                $success = $spawnPointManager->updateSpawnPoint($id, available: $available);
                break;

            case "position":
                if (!$sender instanceof Player) {
                    $sender->sendMessage("§cOnly players can update position to their current location!");
                    return;
                }
                $position = $sender->getPosition()->floor();
                $success = $spawnPointManager->updateSpawnPoint($id, position: $position);
                break;

            default:
                $sender->sendMessage("§cInvalid property! Valid properties: name, world, type, priority, available, position");
                return;
        }

        if ($success) {
            $sender->sendMessage("§aSpawn point updated successfully!");
            $sender->sendMessage("§7Updated §e$property §7to: §f$value");
        } else {
            $sender->sendMessage("§cFailed to update spawn point!");
        }
    }
}
