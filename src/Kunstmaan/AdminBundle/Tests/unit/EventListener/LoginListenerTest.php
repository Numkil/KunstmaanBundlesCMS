<?php

namespace Kunstmaan\AdminBundle\Tests\EventListener;

use Doctrine\ORM\EntityManager;
use Kunstmaan\AdminBundle\Entity\User;
use Kunstmaan\AdminBundle\EventListener\LoginListener;
use Kunstmaan\AdminBundle\Helper\VersionCheck\VersionChecker;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListenerTest extends TestCase
{
    public function testListener()
    {
        $logger = $this->createMock(AbstractLogger::class);
        $version = $this->createMock(VersionChecker::class);
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(User::class);
        $event = $this->createMock(InteractiveLoginEvent::class);
        $em = $this->createMock(EntityManager::class);

        $logger->expects($this->once())->method('info')->willReturn(true);
        $version->expects($this->once())->method('periodicallyCheck')->willReturn(true);
        $event->expects($this->once())->method('getAuthenticationToken')->willReturn($token);
        $token->expects($this->once())->method('getUser')->willReturn($user);

        $listener = new LoginListener($logger, $version, $em);
        $listener->onSecurityInteractiveLogin($event);
    }
}
