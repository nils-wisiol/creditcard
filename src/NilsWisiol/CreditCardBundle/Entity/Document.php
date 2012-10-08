<?php
namespace NilsWisiol\CreditCardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class Document extends Entity
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	public $id;
	
	/**
	 * @Assert\File(maxSize="6000000")
	 */
	public $file;	

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Assert\NotBlank
	 */
	public $name;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	public $path;
	
	/**
	 * @ORM\Column(type="string",length=3)
	 */
	public $format;
	
	/**
	 * @ORM\Column(type="integer")
	 */
	public $duplicates = 0;	
	
	/**
	 * @ORM\ManyToOne(targetEntity="Account")
	 * @var Account
	 */
	public $account;
	
	/**
	 * @ORM\OneToMany(targetEntity="Entry",mappedBy="document",cascade={"remove"})
	 */
	protected $entries;

	public function getAbsolutePath()
	{
		return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;
	}

	public function getWebPath()
	{
		return null === $this->path ? null : $this->getUploadDir().'/'.$this->path;
	}

	protected function getUploadRootDir()
	{
		// the absolute directory path where uploaded documents should be saved
		return __DIR__.'/../../../../web/'.$this->getUploadDir();
	}

	protected function getUploadDir()
	{
		// get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
		return 'uploads/documents';
	}
	
	function __toString() {
		return $this->name;
	}
	
}