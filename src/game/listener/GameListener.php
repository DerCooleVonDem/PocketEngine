<?php

namespace JonasWindmann\PocketEngine\game\listener;

use JonasWindmann\CoreAPI\CoreAPI;
use JonasWindmann\CoreAPI\utils\WorldUtils;
use JonasWindmann\PocketEngine\components\PocketEngineComponent;
use JonasWindmann\PocketEngine\game\Game;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\world\Position;

class GameListener implements Listener
{
    public function onDeath(PlayerDeathEvent $event) {
        $event->setKeepInventory(false);
    }

    public function onRespawn(PlayerRespawnEvent $event) {
        $session = CoreAPI::getInstance()->getSessionManager()->getSessionByPlayer($event->getPlayer());
        if($session === null) return;

        $pocketengineComponent = $session->getComponent("pocketengine");
        if($pocketengineComponent === null && !$pocketengineComponent instanceof PocketEngineComponent) return;

        $world = WorldUtils::getWorldByName("game");
        if($world === null) return;
        $spawnPoint = $pocketengineComponent->getSpawnPoint();
        $event->setRespawnPosition(new Position($spawnPoint->x, $spawnPoint->y, $spawnPoint->z, $world));
    }

    public function onPlayerQuit(PlayerQuitEvent $event) {
        $game = Game::getInstance();
        if ($game) {
            $game->leave($event->getPlayer());
        }
    }
}