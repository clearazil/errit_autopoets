<?php

namespace ProductBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ShoppingBundle\Entity\PurchaseOrderLine;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Product
 *
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="ProductBundle\Repository\ProductRepository")
 */
class Product
{
    /**
     * @ORM\OneToMany(targetEntity="ProductImage", mappedBy="product")
     */
    private $images;

    /**
     * @ORM\ManyToOne(targetEntity="ProductCategory", inversedBy="products")
     * @ORM\JoinColumn(name="product_category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $category;

    /**
     * @var Collection $purchaseOrderLines
     *
     * @ORM\OneToMany(targetEntity="ShoppingBundle\Entity\PurchaseOrderLine", mappedBy="product")
     */
    private $purchaseOrderLines;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="product_category_id", type="integer", nullable=true)
     */
    private $productCategoryId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2)
     */
    private $price;

    /**
     * @var int
     *
     * @ORM\Column(name="stock", type="integer")
     */
    private $stock;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updated_at;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created_at;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Product
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Product
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set price
     *
     * @param float $price
     *
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set stock
     *
     * @param integer $stock
     *
     * @return Product
     */
    public function setStock($stock)
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * Get stock
     *
     * @return int
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Product
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Product
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps()
    {
        $this->setUpdatedAt(new \DateTime('now'));

        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTime('now'));
        }
    }

    /**
     * Add image
     *
     * @param ProductImage $image
     *
     * @return Product
     */
    public function addImage(ProductImage $image)
    {
        $this->images[] = $image;

        return $this;
    }

    /**
     * Remove image
     *
     * @param ProductImage $image
     */
    public function removeImage(ProductImage $image)
    {
        $this->images->removeElement($image);
    }

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @return string
     */
    public function getFirstImageLink()
    {
        $image = $this->getImages()->first();

        if (empty($image)) {
            return ProductImage::PLACEHOLDER_IMAGE;
        }

        return 'img/product/' . $image->getImage();
    }

    /**
     * Set productCategoryId
     *
     * @param integer $productCategoryId
     *
     * @return Product
     */
    public function setProductCategoryId($productCategoryId)
    {
        $this->productCategoryId = $productCategoryId;

        return $this;
    }

    /**
     * Get productCategoryId
     *
     * @return integer
     */
    public function getProductCategoryId()
    {
        return $this->productCategoryId;
    }

    /**
     * Set category
     *
     * @param ProductCategory $category
     *
     * @return Product
     */
    public function setCategory(ProductCategory $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return ProductCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Add purchaseOrderLine
     *
     * @param PurchaseOrderLine $purchaseOrderLine
     *
     * @return Product
     */
    public function addPurchaseOrderLine(PurchaseOrderLine $purchaseOrderLine)
    {
        $this->purchaseOrderLines[] = $purchaseOrderLine;

        return $this;
    }

    /**
     * Remove purchaseOrderLine
     *
     * @param PurchaseOrderLine $purchaseOrderLine
     */
    public function removePurchaseOrderLine(PurchaseOrderLine $purchaseOrderLine)
    {
        $this->purchaseOrderLines->removeElement($purchaseOrderLine);
    }

    /**
     * Get purchaseOrderLines
     *
     * @return Collection
     */
    public function getPurchaseOrderLines()
    {
        return $this->purchaseOrderLines;
    }
}
