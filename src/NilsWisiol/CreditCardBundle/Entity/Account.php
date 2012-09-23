<?php
namespace NilsWisiol\CreditCardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Persistence\PersistentObject;

/**
 * @ORM\Entity
 *
 */
class Account extends Entity {
	
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
	
	function __toString() {
		return $this->name;
	}
	
}