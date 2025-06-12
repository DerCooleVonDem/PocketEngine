<?php

namespace JonasWindmann\PocketEngine\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use pocketmine\command\CommandSender;

class InfoSubCommand extends SubCommand
{

    public function __construct()
    {
        parent::__construct("info", "Show information about PocketEngine", "/pocketengine info", 0, 0, "pocketengine.use");
    }

    public function execute(CommandSender $sender, array $args): void
    {
        $sender->sendMessage("PocketEngine is a plugin that provides a set of APIs to allow easy creation of MiniGames.");
        $sender->sendMessage("It is currently in development and not yet ready for use.");
        $sender->sendMessage("For more information, visit https://github.com/JonasWindmann/PocketEngine");
    }
}