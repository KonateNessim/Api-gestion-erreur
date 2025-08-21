<?php

namespace App\Repository;

use App\Entity\ErrorTicket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ErrorTicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ErrorTicket::class);
    }

    public function add(ErrorTicket $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ErrorTicket $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByStatusAndType(string $status, string $type): array
    {
/* dd($type); */
        if ($type != "set") {
            return $this->createQueryBuilder('e')
                 ->andWhere('e.status != :status')
                 ->andWhere('e.type = :type')
                ->setParameter('status', $status)
                ->setParameter('type', $type)
                ->orderBy('e.id', 'DESC')
                ->getQuery()
                ->getResult();
        } else {
            return $this->createQueryBuilder('e')
                ->andWhere('e.status = :status')
                ->setParameter('status', $status)
                ->orderBy('e.id', 'DESC')
                ->getQuery()
                ->getResult();
        }
    }
}
