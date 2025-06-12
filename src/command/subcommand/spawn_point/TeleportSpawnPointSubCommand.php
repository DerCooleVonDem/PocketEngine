<?php

declare(strict_types=1);

namespace JonasWindmann\PocketEngine\command\subcommand\spawn_point;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\PocketEngine\Main;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\Server;

/**
 * Subcommand for teleporting to spawn points
 */
class TeleportSpawnPointSubCommand extends SubCommand
{
    public function __construct()
    {
        parent::__construct(
            "teleport",
            "Teleport to a spawn point",
            "/spawnpoint teleport <id>",
            1,
            1,
            "pocketengine.spawnpoint.teleport"
        );
    }

    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cThis command can only be used by players.");
            return;
        }

        $id = $args[0];
        $spawnPointManager = Main::getInstance()->getSpawnPointManager();

        // Check if spawn point exists
        $spawnPoint = $spawnPointManager->getSpawnPoint($id);
        if ($spawnPoint === null) {
            $sender->sendMessage("§cSpawn point with ID '$id' not found!");
            $sender->sendMessage("§7Use '/spawnpoint list' to see all spawn points.");
            return;
        }

        // Get the world
        $worldName = $spawnPoint->getWorldName();
        $world = null;

        if ($worldName !== null) {
            $world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);
            if ($world === null) {
                $sender->sendMessage("§cWorld '$worldName' is not loaded!");
                return;
            }
        } else {
            // Use current world if no world specified
            $world = $sender->getWorld();
        }

        // Create position and teleport
        $position = new Position(
            $spawnPoint->getPosition()->x + 0.5,
            $spawnPoint->getPosition()->y,
            $spawnPoint->getPosition()->z + 0.5,
            $world
        );

        $sender->teleport($position);
        $sender->sendMessage("§aTeleported to spawn point: §f{$spawnPoint->getName()}");
        $sender->sendMessage("§7Position: §f" . 
            round($position->x, 1) . ", " . 
            round($position->y, 1) . ", " . 
            round($position->z, 1) . 
            " in " . $world->getFolderName());
    }
}
