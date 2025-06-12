<?php

namespace JonasWindmann\PocketEngine\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\utils\WorldUtils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class SwitchWorldSubCommand extends SubCommand {

    public function __construct()
    {
        parent::__construct(
            "switchworld",
            "Switch to another world",
            "/pocketengine switchworld <world_name>",
            1,
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

        if (count($args) < 1) {
            $sender->sendMessage("§cUsage: " . $this->getUsage());
            return;
        }

        $worldName = $args[0];
        $world = WorldUtils::getWorldByName($worldName);

        if ($world === null) {
            $sender->sendMessage("§cWorld not found.");
            return;
        }

        $sender->teleport($world->getSpawnLocation());
        $sender->sendMessage("§aSwitched to world: " . $world->getFolderName());
    }
}