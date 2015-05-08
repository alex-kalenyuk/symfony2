<?php

namespace TrainingBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use adLDAP\adLDAP;

class UserRepository extends EntityRepository implements UserProviderInterface
{
    public function loadUserByUsername($username)
    {
        $user = $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$user) {
            $message = sprintf(
                'Unable to find AppBundle:User object identified by "%s".',
                $username
            );
            throw new UsernameNotFoundException($message);
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    $class
                )
            );
        }

        return $this->find($user->getId());
    }

    public function supportsClass($class)
    {
        return $this->getEntityName() === $class
        || is_subclass_of($class, $this->getEntityName());
    }    
    
    public function findManagerGUID($adLdap, $dn)
    {
        $filter = "(&(objectClass=user)(samaccounttype=" . adLDAP::ADLDAP_NORMAL_ACCOUNT .")(objectCategory=person)(distinguishedname=".$dn."))";
        $sr = ldap_search($adLdap->getLdapConnection(), $adLdap->getBaseDn(), $filter, ["objectGUID"]);
        $entries = ldap_get_entries($adLdap->getLdapConnection(), $sr);
        if (isset($entries["count"]) && $entries["count"] > 0) {
            return $adLdap->utilities()->decodeGuid($entries[0]["objectguid"][0]);
        }
        
        return NULL;
    }
}