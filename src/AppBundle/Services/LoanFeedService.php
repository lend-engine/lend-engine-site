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
        $builder = $loanRepo->createQueryBuilder('l');
        $builder->select('l');
        $builder->join('l.library', 't');
        $builder->where('t.feedOptOut != 1');
        $builder->andWhere("l.image LIKE '%jpg'");
        $builder->setMaxResults(6);
        $builder->orderBy('l.createdAt', 'DESC');
        $query = $builder->getQuery();
        $loans = $query->getResult();

        return $loans;
    }
}