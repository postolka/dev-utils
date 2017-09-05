<?php
namespace Ipsum;

use Faker;

class ExtendedProvider extends Faker\Provider\Base {
	protected $locale;
	protected $lng = NULL;
/** @var Faker\Generator  */
	protected $generator;

	public function __construct($generator, $locale){
		parent::__construct($generator);
		$this->locale = $locale;
		if (preg_match('/^([a-z]{2})/', $locale, $match) && !empty($match[1]))
			$this->lng = $match[1];
		$this->generator = $generator;
	}
	public function dateNum(){
		setlocale(LC_TIME, $this->lng);
		$val = strftime('%x', Faker\Provider\DateTime::unixTime());
		setlocale(LC_TIME, 'C');
		return $val;
	}

	public function constant(){
		return getPost('value');
	}

	public function login(){
		$p = new Faker\Provider\Person($this->generator);
		return 'x'.$p->firstName();
	}
}

abstract class RandField{
	protected $typeID = 'default';
	protected $faker;

/**
 * @param \Faker\Generator $faker
 */
	function __construct($faker){
		$this->faker = $faker;
	}
	function getType(){return $this->typeID; }
	abstract function getData();
	function getHTML(){
		$value  = htmlentities($this->getData());
		return <<<HTML
<input type="text" name="$this->name" value="$value"/>
HTML;
	}
}
