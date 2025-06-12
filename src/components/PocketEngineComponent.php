<?php

namespace JonasWindmann\PocketEngine\components;

use JonasWindmann\CoreAPI\session\BasePlayerSessionComponent;
use JonasWindmann\PocketEngine\game\Game;
use JonasWindmann\PocketEngine\Main;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\math\Vector3;

class PocketEngineComponent extends BasePlayerSessionComponent
{

    public bool $isInSetupMode = false;

    public Vector3 $spawnPoint;

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return "pocketengine";
    }

    public function onCreate(): void
    {
        $this->isInSetupMode = Main::getInstance()->getConfigurationManager()->isSetupMode();

        if($this->isInSetupMode) {
            $this->getPlayer()->sendMessage("§aThis server is in setup mode.");
        }

        Game::getInstance()->join($this->getPlayer());
    }

    public function setSpawnPoint(\pocketmine\math\Vector3 $vector3)
    {
        $this->spawnPoint = $vector3;
    }

    public function getSpawnPoint(): \pocketmine\math\Vector3
    {
        return $this->spawnPoint ?? new Vector3(0, 5, 0);
    }

    /**
     * @return bool
     */
    public function isInSetupMode(): bool
    {
        return $this->isInSetupMode;
    }
}