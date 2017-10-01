<?php

namespace ProductBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ProductCategoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProductCategoryRepository extends EntityRepository
{
    /**
     * @return \Doctrine\ORM\Query
     */
    public function categoriesQuery()
    {
        return $this->createQueryBuilder('product_category')
            ->getQuery();
    }
}
