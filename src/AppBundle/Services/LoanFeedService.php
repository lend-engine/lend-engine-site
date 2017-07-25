<?php

namespace AppBundle\Services;

use Doctrine\ORM\EntityManager;

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
        $loans = $loanRepo->findBy([], ['createdAt' => 'DESC'], 6);
        return $loans;
    }
}