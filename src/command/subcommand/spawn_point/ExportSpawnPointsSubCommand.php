<?php

declare(strict_types=1);

namespace JonasWindmann\PocketEngine\command\subcommand\spawn_point;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\PocketEngine\Main;
use pocketmine\command\CommandSender;

/**
 * Subcommand for exporting spawn points to a file
 */
class ExportSpawnPointsSubCommand extends SubCommand
{
    public function __construct()
    {
        parent::__construct(
            "export",
            "Export spawn points to a file",
            "/spawnpoint export [filename]",
            0,
            1,
            "pocketengine.spawnpoint.export"
        );
    }

    public function execute(CommandSender $sender, array $args): void
    {
        $spawnPointManager = Main::getInstance()->getSpawnPointManager();
        $spawnPoints = $spawnPointManager->getAllSpawnPoints();

        if (empty($spawnPoints)) {
            $sender->sendMessage("§cNo spawn points to export!");
            return;
        }

        // Generate filename
        $filename = $args[0] ?? "spawn_points_" . date("Y-m-d_H-i-s") . ".json";
        if (!str_ends_with($filename, ".json")) {
            $filename .= ".json";
        }

        // Create exports directory
        $exportDir = Main::getInstance()->getDataFolder() . "exports/";
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $exportPath = $exportDir . $filename;

        // Export spawn points
        $exportData = $spawnPointManager->exportSpawnPoints();
        $jsonData = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if (file_put_contents($exportPath, $jsonData) === false) {
            $sender->sendMessage("§cFailed to export spawn points!");
            return;
        }

        $sender->sendMessage("§aSpawn points exported successfully!");
        $sender->sendMessage("§7File: §f$filename");
        $sender->sendMessage("§7Location: §f$exportPath");
        $sender->sendMessage("§7Exported: §a" . count($exportData) . " §7spawn points");
    }
}
