<?php
namespace NilsWisiol\CreditCardBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Persistence\PersistentObject;

/**
 * @ORM\Entity
 *
 */
class Category extends Entity {
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @ORM\Column(type="string")
	 */
	protected $name;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Category",inversedBy="children")
	 * @var unknown_type
	 */
	protected $parent;
	
	/**
	 * @ORM\OneToMany(targetEntity="Category",mappedBy="parent")
	 * @var ArrayCollection
	 */
	protected $children;
	
	/**
	 * @ORM\OneToMany(targetEntity="Entry",mappedBy="category")
	 * @var ArrayCollection
	 */
	protected $entries;
	
	function __toString() {
		if ($this->parent == null) {
			return $this->name;
		} else {
			return $this->parent . "/" . $this->name;
		}
	}
	
}