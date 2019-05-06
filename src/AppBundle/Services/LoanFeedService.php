<?php

namespace AppBundle\Services;

use Doctrine\ORM\EntityManager;
use \Doctrine\Common\Collections\Criteria;

class LoanFeedService
{
    /** @var EntityManager  */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getLoans()
    {
        /** @var \AppBundle\Repository\CoreLoanRepository $loanRepo */
        $loanRepo = $this->em->getRepository('AppBundle:CoreLoan');

        // Don't show loans from CrispVideo
        // Hard coded for now... open this up to something more scalable and DB related when the need arises
        $crispVideo = $this->em->getReference(\AppBundle\Entity\Tenant::class, 423);

        $criteria = new Criteria();
        $criteria->where(Criteria::expr()->neq('library', $crispVideo));
        $criteria->setMaxResults(6);
        $criteria->orderBy(['createdAt' => SORT_DESC]);

        $loans = $loanRepo->matching($criteria);

        return $loans;
    }
}