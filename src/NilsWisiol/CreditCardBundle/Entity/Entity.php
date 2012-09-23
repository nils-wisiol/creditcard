<?php

namespace NilsWisiol\CreditCardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * 
 * @author nils
 * 
 * @ORM\MappedSuperclass  
 * @ORM\HasLifecycleCallbacks
 */
class Entity {
	
	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $created;
	
	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $changed;	
	
	/**
	 * @ORM\PreUpdate
	 */
	public function preUpdate()
	{
		$this->changed = new \DateTime(); 
	}	
	
	/**
	 * @ORM\PrePersist
	 */
	public function prePersist()
	{
		$this->created = $this->changed = new \DateTime();
	}
	
	/**
	 * Sets a persistent fields value.
	 *
	 * @param string $field
	 * @param array $args
	 *
	 * @return void
	 */
	private function set($field, $args)
	{
		$this->$field = $args[0];
	}
	
	/**
	 * Get persistent field value.
	 *
	 * @param string $field
	 *
	 * @return mixed
	 */
	private function get($field)
	{
		return $this->$field;
	}
	
	/**
	 * Magic method that implements
	 *
	 * @param string $method
	 * @param array $args
	 *
	 * @throws \BadMethodCallException
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		$command = substr($method, 0, 3);
		$field = lcfirst(substr($method, 3));
		if ($command == "set") {
			$this->set($field, $args);
		} else if ($command == "get") {
			return $this->get($field);
		} else {
			throw new \BadMethodCallException("There is no method ".$method);
		}
	}	
	
}