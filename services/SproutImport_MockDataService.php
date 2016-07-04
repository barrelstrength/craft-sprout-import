<?php
namespace Craft;

/**
 * Class SproutImport_MockDataService
 *
 * Various methods to help with importing mock seed data into fields and elements
 *
 * @package Craft
 */
class SproutImport_MockDataService extends BaseApplicationComponent
{
	/**
	 * Get a random sample of Relations for the given Element Relations field
	 *
	 * @param       $elementName
	 * @param array $attributes
	 * @param       $limit
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getMockRelations($elementName, array $attributes = array(), $limit = 1)
	{
		$criteria = craft()->elements->getCriteria($elementName);
		$results  = $criteria->find($attributes);

		$total = $criteria->total();

		// If limit is greater than the total number of elements, use total
		if ($limit > $total || $limit === '')
		{
			$limit = $total;
		}

		$randomLimit = rand(1, $limit);

		$randomKeys = array_rand($results, $randomLimit);

		$keys = (!is_array($randomKeys)) ? array($randomKeys) : $randomKeys;

		$elementIds = array();

		if (!empty($keys))
		{
			foreach ($keys as $key)
			{
				$elementIds[] = $results[$key]->id;
			}
		}

		return $elementIds;
	}

	/**
	 * Get mock data for fields when generating a mock Element
	 *
	 * @param $elementName
	 *
	 * @return array
	 */
	public function getMockFieldsByElementName($elementName)
	{
		$fieldClasses = sproutImport()->getSproutImportFields();

		$fieldValues = array();

		if (!empty($fieldClasses))
		{
			// Get only declared field classes
			foreach ($fieldClasses as $fieldClass)
			{
				$fields = sproutImport()->elements->getFieldsByType($elementName, $fieldClass);

				if (!empty($fields))
				{
					// Loop through all attach fields on this element
					foreach ($fields as $field)
					{
						$fieldClass->setField($field);
						$fieldHandle               = $field->handle;
						$fieldValues[$fieldHandle] = $fieldClass->getMockData();
					}
				}
			}
		}

		return $fieldValues;
	}

	/**
	 * Get Element Group IDs from sources setting
	 *
	 * @param $sources
	 *
	 * @return array
	 */
	public function getElementGroupIds($sources)
	{
		$ids = array();

		if (!empty($sources))
		{
			if ($sources == "*")
			{
				return $sources;
			}

			foreach ($sources as $source)
			{
				$ids[] = $this->getElementGroupId($source);
			}
		}

		return $ids;
	}

	/**
	 * Get Element Group
	 *
	 * @param $source
	 *
	 * @return mixed
	 */
	public function getElementGroupId($source)
	{
		$sourceExplode = explode(":", $source);

		return $sourceExplode[1];
	}
	
	/**
	 * Return a random selection of items from an array for fields such as Multi-select and Checkboxes
	 *
	 * @param $values
	 * @param $number
	 *
	 * @return array|mixed
	 */
	public function getRandomArrays($values, $number)
	{
		$rands = array_rand($values, $number);

		if (!is_array($rands))
		{
			return array($rands);
		}

		return $rands;
	}

	/**
	 * Return selected values by keys for use with fields such as Multi-select and Checkboxes
	 *
	 * @param $keys
	 * @param $options
	 *
	 * @return array
	 */
	public function getOptionValuesByKeys($keys, $options)
	{
		$values = array();

		foreach ($keys as $key)
		{
			$values[] = $options[$key]['value'];
		}

		return $values;
	}

	/**
	 * Return a single random value for a set of given options
	 *
	 * @param        $options
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getRandomOptionValue($options, $key = 'value')
	{
		$randKey = array_rand($options, 1);

		$value = $options[$randKey];

		if ($key == false)
		{
			return $value;
		}

		return $value[$key];
	}

	/**
	 * Generate a fake time for the DateSproutImportFieldImporter Class
	 *
	 * @param $time
	 * @param $increment
	 *
	 * @return string
	 */
	public function getMinutesByIncrement($time, $increment)
	{
		$hour    = date('g', $time);
		$minutes = date('i', $time);
		$amPm    = date('A', $time);

		$timeMinute = $minutes - ($minutes % $increment);

		if ($timeMinute === 0)
		{
			$timeMinute = "00";
		}

		return $hour . ":" . $timeMinute . " " . $amPm;
	}

	/**
	 * Generate columns for the TableSproutImportFieldImporter
	 *
	 * @param $columns
	 *
	 * @return array
	 */
	public function generateColumns($columns)
	{
		$values = array();

		foreach ($columns as $key => $column)
		{
			$values[$key] = $this->generateColumn($key, $column);
		}

		return $values;
	}

	/**
	 * Generate a specific column for the TableSproutImportFieldImporter
	 *
	 * @param $key
	 * @param $column
	 *
	 * @return array|int|string
	 */
	public function generateColumn($key, $column)
	{
		$value        = '';
		$fakerService = sproutImport()->faker->getGenerator();

		if (!empty($column))
		{
			$type = $column['type'];

			switch ($type)
			{
				case "singleline":

					$value = $fakerService->text(50);

					break;

				case "multiline":
					$lines = rand(2, 4);

					$value = $fakerService->sentences($lines, true);

					break;

				case "number":

					$value = $fakerService->randomDigit;

					break;

				case "checkbox":

					$bool = rand(0, 1);

					if ($bool === 0)
					{
						$value = '';
					}

					$value = $bool;

					break;
			}
		}

		return $value;
	}
}
