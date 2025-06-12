<?php

namespace JonasWindmann\PocketEngine\command;

use JonasWindmann\CoreAPI\command\BaseCommand;
use JonasWindmann\PocketEngine\command\subcommand\InfoSubCommand;
use JonasWindmann\PocketEngine\command\subcommand\SetSpawnPointSubCommand;
use JonasWindmann\PocketEngine\command\subcommand\SwitchWorldSubCommand;

class PocketEngineCommand extends BaseCommand
{
    public function __construct()
    {
        parent::__construct(
            "pocketengine",
            "PocketEngine command",
            "/pocketengine <subcommand>",
            ["pe", "pocket"],
            "pocketengine.use"
        );

        $this->registerSubCommands([
            new InfoSubCommand(),
            new SwitchWorldSubCommand(),
            new SetSpawnPointSubCommand()
        ]);
    }
}