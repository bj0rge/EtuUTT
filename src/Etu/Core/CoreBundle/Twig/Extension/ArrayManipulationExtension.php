<?php

namespace Etu\Core\CoreBundle\Twig\Extension;

/**
 * ArrayManipulationExtension
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class ArrayManipulationExtension extends \Twig_Extension
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'array_maniplation';
	}

	/**
	 * @return array
	 */
	public function getFilters()
	{
		return array(
			'shuffle' => new \Twig_Filter_Method($this, 'shuffle'),
		);
	}

	/**
	 * Shuffle an array
	 *
	 * @param array $array
	 * @return array
	 */
	public static function shuffle(array $array)
	{
		shuffle($array);
		return $array;
	}
}
