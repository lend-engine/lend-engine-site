<?php

namespace AppBundle\Entity;

/**
 * AccountRepository
 *
 */
class AccountRepository extends \Doctrine\ORM\EntityRepository
{

    /**
     * @return array
     */
    public function findOneBySchema($schema)
    {
        return $this->getEntityManager()
            ->createQuery("SELECT t FROM AppBundle:Account t WHERE dbSchema = '{$schema}'")
            ->getResult();
    }

}
