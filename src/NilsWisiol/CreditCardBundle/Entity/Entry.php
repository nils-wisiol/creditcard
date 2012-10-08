<?php
namespace NilsWisiol\CreditCardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Persistence\PersistentObject;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 */
class Entry extends Entity {
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\Column(type="date")
	 */
	protected $date;
	
	/**
	 * @ORM\Column(type="date",nullable=true)
	 */
	protected $dateReceipt;
	
	/**
	 * @ORM\Column(type="string",length=200,name="description")
	 */
	protected $desc;
	
	/**
	 * @ORM\Column(type="string",length=400,nullable=true)
	 */
	protected $note;
	
	/**
	 * @ORM\Column(type="decimal",precision=9,scale=2)
	 */
	protected $amount;
	
	/**
	 * @ORM\Column(type="string",length=3)
	 */
	protected $cur;
	
	/**
	 * @ORM\Column(type="decimal",precision=9,scale=2,nullable=true)
	 */
	protected $amountOrg;
	
	/**
	 * @ORM\Column(type="string",length=3,nullable=true)
	 */
	protected $curOrg;
	
	/**
	 * @ORM\Column(type="integer",nullable=true)
	 */
	protected $idAcc;
	
	/**
	 * @ORM\Column(type="string",length=32)
	 */
	protected $hash;
	
	/**
	 * @ORM\Column(type="string",length=1024)
	 */
	protected $hashinv;	
	
	/**
	 * @ORM\ManyToOne(targetEntity="Account")
	 * @var Account
	 */
	protected $account;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Document")
	 * @var Document
	 */
	protected $document;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Category")
	 * @var Category
	 */
	protected $category;
	
	/**
	 * @ORM\PreUpdate
	 * @ORM\PrePersist
	 */
	public function generateHash() {
		$this->hashinv = strtoupper($this->desc).number_format(floor($this->amount*100),0,"","").$this->date->format('d.m.Y');
		$this->hash = md5($this->hashinv);
	}	
	
}