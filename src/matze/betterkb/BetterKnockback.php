<?php

namespace matze\betterkb;

use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

class BetterKnockback extends PluginBase implements Listener {

    /** @var array */
    private $cooldown = [];

    /**
     *
     */

    public function onEnable(): void
    {
        Server::getInstance()->getPluginManager()->registerEvents($this, $this);
    }

    /**
     * @priority HIGH
     * @param EntityDamageByEntityEvent $event
     */

    public function onEntityAttack(EntityDamageByEntityEvent $event): void {
        $player = $event->getEntity();
        $damager = $event->getDamager();

        if ($event->isCancelled()) {
            return;
        }
        if (!$player instanceof Player) {
            return;
        }
        $name = $player->getName();
        if (!isset($this->cooldown[$name]))
            $this->cooldown[$name] = Server::getInstance()->getTick();
        if (!$damager instanceof Player) {
            return;
        }
        if ($player->isCreative(true)) {
            return;
        }

        $item = $damager->getInventory()->getItemInHand();

        if ($item->hasEnchantment(Enchantment::KNOCKBACK)) {
            $event->setModifier(0, EntityDamageByEntityEvent::MODIFIER_TOTEM);
            if ($this->cooldown[$name] <= Server::getInstance()->getTick()) {
                $multiplier = 1.4;
                if ($item->getEnchantment(Enchantment::KNOCKBACK)->getLevel() === 2) {
                    $multiplier = 1.7;
                }
                $event->setCancelled();
                $player->broadcastEntityEvent(ActorEventPacket::HURT_ANIMATION);
                $player->setMotion(new Vector3($damager->getDirectionVector()->x / $multiplier, $multiplier - 0.95, $damager->getDirectionVector()->z / $multiplier));
                $this->cooldown[$player->getName()] = Server::getInstance()->getTick() + 10;
            } else {
                $event->setCancelled();
            }
        }
    }
}
