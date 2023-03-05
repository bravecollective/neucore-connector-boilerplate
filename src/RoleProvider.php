<?php

namespace Brave\CoreConnector;

use Brave\NeucoreApi\Api\ApplicationGroupsApi;
use Brave\NeucoreApi\ApiException;
use Eve\Sso\EveAuthentication;
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

    private ApplicationGroupsApi $api;

    private Helper $session;

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

        /* @var ?EveAuthentication $eveAuth */
        $eveAuth = $this->session->get('eveAuth');
        if ($eveAuth === null) {
            return $this->cacheRoles($roles);
        }

        // try cache
        $coreGroups = $this->session->get('coreGroups');
        if (is_array($coreGroups) && $coreGroups['time'] > (time() - 60*60)) {
            return $coreGroups['roles'];
        }

        // get groups from Core
        try {
            $groups = $this->api->groupsV2($eveAuth->getCharacterId());
            // If you need groups based on corporation membership for characters that were not added
            // to Core, use https://account.bravecollective.com/api.html#/Application/groupsWithFallbackV1
        } catch (ApiException $ae) {
            // Don't log "404 Character not found." error from Core.
            if ($ae->getCode() !== 404 || !str_contains($ae->getMessage(), 'Character not found.')) {
                error_log((string)$ae);
            }
            return $this->cacheRoles($roles, -1);
        }
        foreach ($groups as $group) {
            $roles[] = 'core:' . $group->getName();
        }

        return $this->cacheRoles($roles);
    }

    public function getCachedRoles(): array
    {
        $coreGroups = $this->session->get('coreGroups');
        return $coreGroups['roles'] ?? [];
    }

    public function clearCache(): void
    {
        $this->session->set('coreGroups', null);
    }

    private function cacheRoles(array $roles, int $expires = null): array
    {
        $this->session->set('coreGroups', [
            'time' => $expires ?: time(),
            'roles' => $roles
        ]);
        return $roles;
    }
}
