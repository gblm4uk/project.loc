<?php

namespace App\Repository;

use App\Entity\Items;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Items|null find($id, $lockMode = null, $lockVersion = null)
 * @method Items|null findOneBy(array $criteria, array $orderBy = null)
 * @method Items[]    findAll()
 * @method Items[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Items::class);
    }

    /**
     * @param array $param
     *
     * @return array
     */
    public function findSorted(array $param): array
    {
        $qb = $this->createQueryBuilder('i');

        $field = in_array($param[0], Items::SORT_FIELDS) ? $param[0] : 'id';
        $param[1] = $param[1] ?? 'default';
        $sort = in_array(strtoupper($param[1]), ['ASC', 'DESC']) ? strtoupper($param[1]) : 'ASC';
        $qb->orderBy('i.'.$field, $sort);

        return $qb->getQuery()->execute();
    }
}
