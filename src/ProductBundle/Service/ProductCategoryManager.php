<?php

namespace ProductBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Paginator;

class ProductCategoryManager
{
    private $em;

    private $paginator;

    public function __construct(EntityManagerInterface $em, Paginator $paginator)
    {
        $this->em = $em;
        $this->paginator = $paginator;
    }

    /**
     * @param Request $request
     * @return \Knp\Component\Pager\Pagination\PaginationInterface
     * @throws \LogicException
     */
    public function paginatedCategories(Request $request)
    {
        $query = $this->em->getRepository('ProductBundle:ProductCategory')
            ->categoriesQuery();

        $productCategories = $this->paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        return $productCategories;
    }
}