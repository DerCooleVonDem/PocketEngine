<?php

namespace JonasWindmann\PocketEngine\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\PocketEngine\Main;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class SetSpawnPointSubCommand extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "setspawnpoint",
            "Add a spawn point at your current location (deprecated - use /spawnpoint add)",
            "/pocketengine setspawnpoint [name]",
            0,
            1,
            "pocketengine.use"
        );
    }

    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cThis command can only be used by players.");
            return;
        }

        // Show deprecation warning
        $sender->sendMessage("§eWarning: This command is deprecated!");
        $sender->sendMessage("§7Please use '/spawnpoint add' instead for better spawn point management.");

        $position = $sender->getPosition()->floor();
        $spawnPointManager = Main::getInstance()->getSpawnPointManager();

        $name = $args[0] ?? null;
        $worldName = $sender->getWorld()->getFolderName();

        $id = $spawnPointManager->addSpawnPoint($position, $name, $worldName);
        $sender->sendMessage("§aSpawn point added with ID: §e$id");
        $sender->sendMessage("§7Position: §f" . $position->x . ", " . $position->y . ", " . $position->z);
        $sender->sendMessage("§7World: §f$worldName");
        $sender->sendMessage("§7Use '/spawnpoint info $id' for more details.");
    }
}