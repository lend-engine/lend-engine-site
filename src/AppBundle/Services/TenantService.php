<?php
namespace AppBundle\Services;

use Doctrine\ORM\EntityManager;

/**
 * Class TenantInformation
 * @package AppBundle\Extensions
 */
class TenantService
{

    /** @var EntityManager  */
    private $entityManager;

    function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param $accountCode
     * @return bool|null|object
     */
    public function getTenantByAccountCode($accountCode)
    {
        $repo = $this->entityManager->getRepository("AppBundle:Tenant");
        if ($tenant = $repo->findOneBy(['stub' => $accountCode])) {
            return $tenant;
        } else {
            return false;
        }
    }

}