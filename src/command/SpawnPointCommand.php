<?php

declare(strict_types=1);

namespace JonasWindmann\PocketEngine\command;

use JonasWindmann\CoreAPI\command\BaseCommand;
use JonasWindmann\PocketEngine\command\subcommand\spawn_point\AddSpawnPointSubCommand;
use JonasWindmann\PocketEngine\command\subcommand\spawn_point\ListSpawnPointsSubCommand;
use JonasWindmann\PocketEngine\command\subcommand\spawn_point\RemoveSpawnPointSubCommand;
use JonasWindmann\PocketEngine\command\subcommand\spawn_point\InfoSpawnPointSubCommand;
use JonasWindmann\PocketEngine\command\subcommand\spawn_point\UpdateSpawnPointSubCommand;
use JonasWindmann\PocketEngine\command\subcommand\spawn_point\TeleportSpawnPointSubCommand;
use JonasWindmann\PocketEngine\command\subcommand\spawn_point\ClearSpawnPointsSubCommand;
use JonasWindmann\PocketEngine\command\subcommand\spawn_point\ExportSpawnPointsSubCommand;
use JonasWindmann\PocketEngine\command\subcommand\spawn_point\ImportSpawnPointsSubCommand;

/**
 * Main command for comprehensive spawn point management
 * Provides all spawn point operations with consistent naming
 */
class SpawnPointCommand extends BaseCommand
{
    public function __construct()
    {
        parent::__construct(
            "spawnpoint",
            "Manage spawn points for the server",
            "/spawnpoint <add|list|remove|info|update|teleport|clear|export|import> [args...]",
            ["sp", "spawn"],
            "pocketengine.spawnpoint.use"
        );

        // Register all spawn point subcommands
        $this->registerSubCommands([
            new AddSpawnPointSubCommand(),
            new ListSpawnPointsSubCommand(),
            new RemoveSpawnPointSubCommand(),
            new InfoSpawnPointSubCommand(),
            new UpdateSpawnPointSubCommand(),
            new TeleportSpawnPointSubCommand(),
            new ClearSpawnPointsSubCommand(),
            new ExportSpawnPointsSubCommand(),
            new ImportSpawnPointsSubCommand()
        ]);
    }
}
