<?php

declare(strict_types=1);

namespace JonasWindmann\PocketEngine\command\subcommand\spawn_point;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\PocketEngine\Main;
use pocketmine\command\CommandSender;

/**
 * Subcommand for listing all spawn points
 */
class ListSpawnPointsSubCommand extends SubCommand
{
    public function __construct()
    {
        parent::__construct(
            "list",
            "List all spawn points",
            "/spawnpoint list [world] [type] [page]",
            0,
            3,
            "pocketengine.spawnpoint.list"
        );
    }

    public function execute(CommandSender $sender, array $args): void
    {
        $spawnPointManager = Main::getInstance()->getSpawnPointManager();
        $allSpawnPoints = $spawnPointManager->getAllSpawnPoints();

        if (empty($allSpawnPoints)) {
            $sender->sendMessage("§cNo spawn points found!");
            $sender->sendMessage("§7Use '/spawnpoint add' to create your first spawn point.");
            return;
        }

        // Parse filters
        $worldFilter = $args[0] ?? null;
        $typeFilter = $args[1] ?? null;
        $page = isset($args[2]) ? max(1, (int) $args[2]) : 1;

        // Apply filters
        $filteredSpawnPoints = $allSpawnPoints;
        
        if ($worldFilter !== null) {
            $filteredSpawnPoints = array_filter($filteredSpawnPoints, 
                fn($sp) => $sp->getWorldName() === $worldFilter);
        }
        
        if ($typeFilter !== null) {
            $filteredSpawnPoints = array_filter($filteredSpawnPoints, 
                fn($sp) => $sp->getType() === $typeFilter);
        }

        if (empty($filteredSpawnPoints)) {
            $sender->sendMessage("§cNo spawn points found matching the filters!");
            return;
        }

        // Pagination
        $itemsPerPage = 8;
        $totalItems = count($filteredSpawnPoints);
        $totalPages = ceil($totalItems / $itemsPerPage);
        $page = min($page, $totalPages);

        $start = ($page - 1) * $itemsPerPage;
        $pageItems = array_slice($filteredSpawnPoints, $start, $itemsPerPage, true);

        // Header
        $header = "Spawn Points";
        if ($worldFilter) $header .= " (World: $worldFilter)";
        if ($typeFilter) $header .= " (Type: $typeFilter)";
        $header .= " (Page $page/$totalPages)";

        $sender->sendMessage("§6=== $header ===");

        // List spawn points
        foreach ($pageItems as $spawnPoint) {
            $status = $spawnPoint->isAvailable() ? "§aAvailable" : "§cUnavailable";
            $world = $spawnPoint->getWorldName() ?? "§7Unknown";
            
            $sender->sendMessage("§e{$spawnPoint->getId()} §7- §f{$spawnPoint->getName()}");
            $sender->sendMessage("  §7Position: §f" . 
                round($spawnPoint->getPosition()->x, 1) . ", " . 
                round($spawnPoint->getPosition()->y, 1) . ", " . 
                round($spawnPoint->getPosition()->z, 1));
            $sender->sendMessage("  §7World: §f$world §7| Type: §f{$spawnPoint->getType()} §7| Priority: §f{$spawnPoint->getPriority()} §7| Status: $status");
        }

        $sender->sendMessage("§7Total: §a$totalItems §7spawn points");
        
        if ($totalPages > 1) {
            $sender->sendMessage("§7Use '/spawnpoint list" . 
                ($worldFilter ? " $worldFilter" : "") . 
                ($typeFilter ? " " . ($worldFilter ? $typeFilter : $typeFilter) : "") . 
                " <page>' to view other pages");
        }
    }
}
