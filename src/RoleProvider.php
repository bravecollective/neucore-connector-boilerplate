<?php

namespace Brave\CoreConnector;

use Brave\NeucoreApi\Api\ApplicationGroupsApi;
use Brave\NeucoreApi\ApiException;
use Brave\Sso\Basics\EveAuthentication;
use Psr\Http\Message\ServerRequestInterface;
use SlimSession\Helper;
use Tkhamez\Slim\RoleAuth\RoleProviderInterface;

/**
 * Provides groups from Brave Core from an authenticated user.
 */
class RoleProvider implements RoleProviderInterface
{
    /**
     * This role is always added.
     */
    const ROLE_ANY = 'role:any';

    /**
     * @var ApplicationGroupsApi
     */
    private $api;

    /**
     * @var Helper
     */
    private $session;

    public function __construct(ApplicationGroupsApi $api, Helper $session)
    {
        $this->api = $api;
        $this->session = $session;
    }

    /**
     * @return string[]
     */
    public function getRoles(ServerRequestInterface $request = null): array
    {
        $roles = [self::ROLE_ANY];

        /* @var EveAuthentication $eveAuth */
        $eveAuth = $this->session->get('eveAuth', null);
        if ($eveAuth === null) {
            return $roles;
        }

        // try cache
        $coreGroups = $this->session->get('coreGroups', null);
        if (is_array($coreGroups) && $coreGroups['time'] > (time() - 60*60)) {
            return $coreGroups['roles'];
        }

        // get groups from Core
        try {
            $groups = $this->api->groupsV2($eveAuth->getCharacterId());
        } catch (ApiException $ae) {
            // Don't log "404 Character not found." error from Core.
            if ($ae->getCode() !== 404 || strpos($ae->getMessage(), 'Character not found.') === false) {
                error_log((string)$ae);
            }
            return $roles;
        }
        foreach ($groups as $group) {
            $roles[] = $group->getName();
        }

        // cache roles
        $this->session->set('coreGroups', [
            'time' => time(),
            'roles' => $roles
        ]);

        return $roles;
    }

    public function getCachedRoles(): array
    {
        $coreGroups = $this->session->get('coreGroups');
        return $coreGroups['roles'] ?? [];
    }

    public function clear(): void
    {
        $this->session->set('coreGroups', null);
    }
}
