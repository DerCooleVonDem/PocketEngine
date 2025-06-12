<?php

declare(strict_types=1);

namespace JonasWindmann\PocketEngine\command\subcommand\spawn_point;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\PocketEngine\Main;
use pocketmine\command\CommandSender;

/**
 * Subcommand for importing spawn points from a file
 */
class ImportSpawnPointsSubCommand extends SubCommand
{
    public function __construct()
    {
        parent::__construct(
            "import",
            "Import spawn points from a file",
            "/spawnpoint import <filename> [overwrite]",
            1,
            2,
            "pocketengine.spawnpoint.import"
        );
    }

    public function execute(CommandSender $sender, array $args): void
    {
        $filename = $args[0];
        $overwrite = isset($args[1]) && strtolower($args[1]) === "true";

        // Ensure .json extension
        if (!str_ends_with($filename, ".json")) {
            $filename .= ".json";
        }

        $importPath = Main::getInstance()->getDataFolder() . "exports/" . $filename;

        if (!file_exists($importPath)) {
            $sender->sendMessage("§cImport file not found: $filename");
            $sender->sendMessage("§7Place the file in: " . dirname($importPath));
            return;
        }

        // Read and parse the file
        $jsonData = file_get_contents($importPath);
        if ($jsonData === false) {
            $sender->sendMessage("§cFailed to read import file!");
            return;
        }

        $importData = json_decode($jsonData, true);
        if ($importData === null) {
            $sender->sendMessage("§cInvalid JSON format in import file!");
            return;
        }

        if (!is_array($importData)) {
            $sender->sendMessage("§cImport file must contain an array of spawn points!");
            return;
        }

        // Import spawn points
        $spawnPointManager = Main::getInstance()->getSpawnPointManager();
        $imported = $spawnPointManager->importSpawnPoints($importData, $overwrite);

        if ($imported > 0) {
            $sender->sendMessage("§aSpawn points imported successfully!");
            $sender->sendMessage("§7Imported: §a$imported §7spawn points");
            if (!$overwrite) {
                $sender->sendMessage("§7Note: Existing spawn points were not overwritten");
                $sender->sendMessage("§7Use '/spawnpoint import $filename true' to overwrite existing spawn points");
            }
        } else {
            $sender->sendMessage("§cNo spawn points were imported!");
            $sender->sendMessage("§7This could be because:");
            $sender->sendMessage("§7- All spawn points already exist (use 'true' to overwrite)");
            $sender->sendMessage("§7- The import file contains invalid data");
        }
    }
}
