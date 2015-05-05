<?php

namespace TrainingBundle\Security\User;

use TrainingBundle\Security\User\adUserCustom as adUserCustom;
use Ztec\Security\ActiveDirectoryBundle\Security\User\adUserProvider as adUserProvider;
use Ztec\Security\ActiveDirectoryBundle\Security\User\adUser as adUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use adLDAP\adLDAP;

class adUserProviderCustom extends adUserProvider
{
    protected $usernamePatterns = array();
    protected $recursiveGrouproles = false;
    
    public function loadUserByUsername($username)
    {
        // The password is set to something impossible to find.
        try {
            $userString = $this->getUsernameFromString($username);
            $user       = new adUserCustom($this->getUsernameFromString($userString), uniqid(true) . rand(
                    0,
                    424242
                ), array());
        } catch (\InvalidArgumentException $e) {
            $msg = $this->translator->trans(
                'ztec.security.active_directory.invalid_user',
                array('%reason%' => $e->getMessage())
            );
            throw new UsernameNotFoundException($msg);
        }

        return $user;
    }

    public function fetchData(adUser $adUser, TokenInterface $token, adLDAP $adLdap)
    {
        $connected = $adLdap->connect();
        $isAD      = $adLdap->authenticate($adUser->getUsername(), $token->getCredentials());
        if (!$isAD || !$connected) {
            $msg = $this->translator->trans(
                'ztec.security.active_directory.ad.bad_response',
                array(
                    '%connection_status%' => var_export($connected, 1),
                    '%is_AD%'             => var_export($isAD, 1),
                )
            );
            throw new \Exception(
                $msg
            );
        }
        /** @var adLDAPUserCollection $user */
        $user = $adLdap->user()->infoCollection($adUser->getUsername(), array('*'));

        if ($user) {
            $groups = $adLdap->user()->groups($adUser->getUsername(), $this->recursiveGrouproles);
            $sfRoles = array();
            $sfRolesTemp = array();
            foreach ($groups as $r) {
                if (in_array($r, $sfRolesTemp) === false) {
                    $sfRoles[] = 'ROLE_' . strtoupper(str_replace(' ', '_', $r));
                    $sfRolesTemp[] = $r;
                }
            }
            $adUser->setRoles($sfRoles);
            unset($sfRolesTemp);

            $adUser->setDisplayName($user->displayName);
            $adUser->setEmail($user->mail);
            $adLDAPUtils = new \adLDAP\classes\adLDAPUtils($adLdap);
            $adUser->setSID($adLDAPUtils->getTextSID($user->objectsid));
            
            return true;
        }
    }
}